<?php

namespace App\Services\Importacion;

use App\Models\Sitio;
use App\Models\Ticket;
use App\Models\TicketHistorial;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

/**
 * Importa tickets desde el Excel real de Formosa (o export de Google Sheets).
 *
 * Reglas del negocio:
 *  - NUNCA actualiza: cada fila se inserta como ticket nuevo
 *    (el N° ORDEN se repite en distintos sitios, ej. 402000037727 -> Incone y Eva Peron).
 *  - Refleja SI o SI los campos del Excel (mapeo por nombre de columna).
 *  - ESTADO del Excel "REALIZADO" entra como workflow "resuelto"; el resto entra "nuevo".
 */
class ImportadorTickets
{
    /** Codigo de provincia (hoja Referencias) => nombre. */
    public const PROVINCIAS = [
        'A32' => 'CHACO',
        'A33' => 'CORRIENTES',
        'A34' => 'ENTRE RIOS',
        'A35' => 'MISIONES',
        'A36' => 'FORMOSA',
        'A41' => 'CATAMARCA',
        'A43' => 'JUJUY',
        'A45' => 'RIOJA',
        'A47' => 'SALTA',
        'A48' => 'SANTIAGO DEL ESTERO',
    ];

    /** Hojas con formato de tickets (excluye Referencias y control). */
    public const HOJAS_TICKETS = [
        'Tks Correctivos',
        'Tks Preventivos',
        'MANT (M01PR004)',
        'LIMP (M01PR012)',
        'MANT CORRECTIVO 2022',
    ];

    /** Nombre de columna normalizado => campo del sistema. */
    private const MAPEO_COLUMNAS = [
        'mda' => 'mda',
        'notificacion' => 'mda',
        'mda (mesa de ayuda)' => 'mda',
        'n orden' => 'numero_orden',
        'orden' => 'numero_orden',
        'clase orden' => 'clase_orden',
        'clase de orden' => 'clase_orden',
        'fecha entrada' => 'fecha_entrada',
        'fecha modific.maestro orden' => 'fecha_modificacion_maestro',
        'grupo planificador' => 'grupo_planificador',
        'texto breve' => 'texto_breve',
        'descripcion' => 'descripcion',
        'calle' => 'calle',
        'ubicacion' => 'ubicacion',
        'denominacion de la ubicacion tecnica' => 'ubicacion',
        'provincia' => 'provincia',
        'ano' => 'anio',
        'area de empresa' => 'area_empresa',
        'doc. adj.' => 'doc_adjunto',
        'inicio extrema' => 'inicio_extrema',
        'fecha de inicio extrema' => 'inicio_extrema',
        'fin extrema' => 'fin_extrema',
        'fecha fin extrema' => 'fin_extrema',
        'estatus' => 'estatus',
        'status de usuario' => 'estatus',
        'prioridad' => 'prioridad',
        'texto para prioridad' => 'prioridad',
        'estado' => 'estado',
        'lo resolvio' => 'lo_resolvio',
        'fecha de resolucion' => 'fecha_resolucion',
        'documentacion (foto o doc)' => 'documentacion',
        'comentarios sobre la resolucion del ticket' => 'comentarios',
        'fotos' => 'fotos_texto',
        'comentarios' => 'comentarios_extra',
    ];

    /** Campos que el Excel trae como numero serial de Excel (ej. 44938). */
    private const CAMPOS_FECHA = [
        'fecha_entrada', 'fecha_modificacion_maestro', 'inicio_extrema',
        'fin_extrema', 'fecha_resolucion',
    ];

    /**
     * Lista las hojas del archivo con su cantidad de filas.
     *
     * @return array<string, int>
     */
    public function hojas(string $ruta): array
    {
        $reader = new XlsxReader();
        $reader->open($ruta);

        $resultado = [];
        foreach ($reader->getSheetIterator() as $hoja) {
            $resultado[$hoja->getName()] = iterator_count($hoja->getRowIterator());
        }
        $reader->close();

        return $resultado;
    }

    /**
     * Parsea una hoja completa a filas mapeadas (sin guardar nada).
     *
     * @return array{columnas: array<int, string>, filas: array<int, array>, ignoradas: int}
     */
    public function parsear(string $ruta, string $hoja): array
    {
        $reader = new XlsxReader();
        $reader->open($ruta);

        $encontrada = false;
        $columnas = [];
        $headerMap = [];   // indice => campo del sistema
        $filas = [];
        $numeroFila = 0;
        $ignoradas = 0;

        foreach ($reader->getSheetIterator() as $sheet) {
            if ($sheet->getName() !== $hoja) {
                continue;
            }
            $encontrada = true;

            foreach ($sheet->getRowIterator() as $fila) {
                $numeroFila++;
                $celdas = $fila->toArray();

                // primera fila no vacia = encabezados
                if ($columnas === []) {
                    if ($this->filaVacia($celdas)) {
                        continue;
                    }
                    $columnas = array_map(fn ($c) => $this->normalizar((string) $c), $celdas);
                    foreach ($columnas as $i => $nombre) {
                        $headerMap[$i] = self::MAPEO_COLUMNAS[$nombre] ?? null;
                    }
                    continue;
                }

                if ($columnas !== [] && $this->filaVacia($celdas)) {
                    $ignoradas++;

                    continue;
                }

                $mapeada = $this->mapearFila($celdas, $headerMap);

                // validacion minima: sin N° ORDEN no es ticket
                if (($mapeada['numero_orden'] ?? '') === '') {
                    $ignoradas++;

                    continue;
                }

                $mapeada['_fila'] = $numeroFila;
                $mapeada['_hoja'] = $hoja;
                $mapeada['_duplicado'] = $this->esDuplicado(
                    $mapeada['numero_orden'],
                    $mapeada['ubicacion'] ?? '',
                    $mapeada['fecha_entrada'] ?? null
                );

                $filas[] = $mapeada;
            }
        }
        $reader->close();

        if (! $encontrada) {
            throw new \RuntimeException("La hoja '{$hoja}' no existe en el archivo.");
        }

        return [
            'columnas' => $columnas,
            'filas' => $filas,
            'ignoradas' => $ignoradas,
        ];
    }

    /**
     * Inserta las filas parseadas como tickets NUEVOS (nunca actualiza).
     *
     * @param  array<int, array>  $filas
     * @return array{importados: int, fallidos: array<int, string>}
     */
    public function importar(array $filas, User $usuario, string $hoja): array
    {
        $importados = 0;
        $fallidos = [];

        DB::transaction(function () use ($filas, $usuario, $hoja, &$importados, &$fallidos) {
            foreach ($filas as $i => $fila) {
                try {
                    // transaccion anidada = SAVEPOINT: si la fila falla, se revierte
                    // solo ella y la transaccion general sigue viva (PostgreSQL)
                    DB::transaction(function () use ($fila, $usuario, $hoja, &$importados) {
                        $this->insertarFila($fila, $usuario, $hoja, $importados);
                    });
                } catch (\Throwable $e) {
                    $fallidos[$i] = $e->getMessage();
                }
            }
        });

        return ['importados' => $importados, 'fallidos' => $fallidos];
    }

    private function insertarFila(array $fila, User $usuario, string $hoja, int &$importados): void
    {
        $datos = $fila;
        $datosFila = $datos['_fila'] ?? null;
        unset($datos['_fila'], $datos['_hoja'], $datos['_duplicado']);

        // provincia A36 => codigo + nombre
        $provinciaCodigo = $this->codigoProvincia($datos['provincia'] ?? null)
            ?? $this->codigoProvincia($datos['area_empresa'] ?? null)
            ?? 'A36';
        $datos['provincia_codigo'] = $provinciaCodigo;
        $datos['provincia_nombre'] = self::PROVINCIAS[$provinciaCodigo] ?? strtoupper((string) ($datos['provincia'] ?? ''));
        unset($datos['provincia']);

        // workflow inicial: REALIZADO entra resuelto, el resto nuevo
        $estadoExcel = strtoupper(trim((string) ($datos['estado'] ?? '')));
        if ($estadoExcel === 'REALIZADO') {
            $datos['estado_workflow'] = Ticket::RESUELTO;
            $datos['resuelto_at'] = $datos['fecha_resolucion']
                ? \Carbon\Carbon::parse($datos['fecha_resolucion'])->toDateTimeString()
                : now();
        } else {
            $datos['estado_workflow'] = Ticket::NUEVO;
        }

        $datos['origen_hoja'] = $hoja;
        $datos['fila_origen'] = $datosFila;
        $datos['importado_por'] = $usuario->id;
        $datos['importado_at'] = now();

        $ticket = new Ticket($datos);

        // sitio: match por nombre + provincia (si no existe, se crea)
        $ticket->sitio_id = $this->sitioPara(
            $ticket->ubicacion,
            $ticket->calle,
            $provinciaCodigo
        );

        $ticket->save();

        TicketHistorial::create([
            'ticket_id' => $ticket->id,
            'user_id' => $usuario->id,
            'accion' => 'importado',
            'detalles' => ['hoja' => $hoja, 'fila' => $datosFila],
        ]);

        $importados++;
    }

    // ======================================================================
    //  INTERNOS
    // ======================================================================

    /** @param array<int, mixed> $celdas */
    private function mapearFila(array $celdas, array $headerMap): array
    {
        $resultado = [];

        foreach ($headerMap as $indice => $campo) {
            if ($campo === null) {
                continue;
            }

            $valor = $celdas[$indice] ?? null;
            $resultado[$campo] = $this->convertir($campo, $valor);
        }

        return $resultado;
    }

    private function convertir(string $campo, mixed $valor): mixed
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        // openspout v5 convierte celdas con formato de fecha a DateTimeImmutable
        if ($valor instanceof \DateTimeInterface) {
            return $valor->format('Y-m-d');
        }

        if (in_array($campo, self::CAMPOS_FECHA, true)) {
            return $this->aFecha($valor);
        }

        if ($campo === 'anio') {
            return (int) $valor;
        }

        // numeros largos (N° ORDEN, MDA) -> texto sin decimales
        if (is_float($valor) || is_int($valor)) {
            return (string) (int) $valor;
        }

        $texto = trim((string) $valor);

        // celdas entre comillas dobles (\"Tarea Realizada...\")
        if (str_starts_with($texto, '"') && str_ends_with($texto, '"') && strlen($texto) > 1) {
            $texto = trim(substr($texto, 1, -1));
        }

        return $texto === '' ? null : $texto;
    }

    /** Convierte numero serial de Excel (44938) o texto de fecha a Y-m-d. */
    private function aFecha(mixed $valor): ?string
    {
        if (is_numeric($valor)) {
            $serial = (float) $valor;
            // rango razonable de fechas (1950..2100)
            if ($serial < 18000 || $serial > 73400) {
                return null;
            }
            $timestamp = (int) round(($serial - 25569) * 86400);

            return gmdate('Y-m-d', $timestamp);
        }

        $texto = trim((string) $valor);
        $time = strtotime($texto);

        return $time === false ? null : date('Y-m-d', $time);
    }

    private function normalizar(string $texto): string
    {
        // se quita el simbolo de grado ANTES de transliterar (iconv en Windows lo arruina)
        $texto = str_replace(['°', "\xc2\xb0"], '', $texto);
        $texto = mb_strtolower($texto, 'UTF-8');

        // mapa manual de acentos (iconv //TRANSLIT en Windows da artefactos como "N^0", "A~no")
        $mapa = [
            'á' => 'a', 'à' => 'a', 'ä' => 'a', 'â' => 'a', 'ã' => 'a',
            'é' => 'e', 'è' => 'e', 'ë' => 'e', 'ê' => 'e',
            'í' => 'i', 'ì' => 'i', 'ï' => 'i', 'î' => 'i',
            'ó' => 'o', 'ò' => 'o', 'ö' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ú' => 'u', 'ù' => 'u', 'ü' => 'u', 'û' => 'u',
            'ñ' => 'n', 'ç' => 'c',
        ];
        $texto = strtr($texto, $mapa);
        $texto = preg_replace('/\s+/u', ' ', $texto) ?? $texto;

        return trim($texto);
    }

    /** @param array<int, mixed> $celdas */
    private function filaVacia(array $celdas): bool
    {
        foreach ($celdas as $celda) {
            if ($celda instanceof \DateTimeInterface) {
                return false; // una fecha ya es contenido
            }
            if ($celda !== null && trim((string) $celda) !== '') {
                return false;
            }
        }

        return true;
    }

    private function codigoProvincia(?string $valor): ?string
    {
        if (! $valor) {
            return null;
        }
        $codigo = strtoupper(trim($valor));

        return isset(self::PROVINCIAS[$codigo]) ? $codigo : null;
    }

    /** Busca el sitio por nombre+provincia; si no existe lo crea. */
    private function sitioPara(?string $nombre, ?string $calle, string $provinciaCodigo): ?int
    {
        $nombre = $nombre ? trim($nombre) : null;
        if (! $nombre) {
            return null;
        }

        $sitio = Sitio::query()
            ->where('nombre', $nombre)
            ->where('provincia_codigo', $provinciaCodigo)
            ->first();

        if (! $sitio) {
            $sitio = Sitio::create([
                'nombre' => $nombre,
                'calle' => $calle,
                'provincia_codigo' => $provinciaCodigo,
                'provincia_nombre' => self::PROVINCIAS[$provinciaCodigo] ?? strtoupper($provinciaCodigo),
            ]);
        } elseif ($sitio->calle === null && $calle) {
            $sitio->calle = $calle;
            $sitio->save();
        }

        return $sitio->id;
    }

    /** Aviso de posible repetido (el sistema igual inserta como ticket nuevo). */
    private function esDuplicado(string $numeroOrden, ?string $ubicacion, ?string $fechaEntrada): bool
    {
        return Ticket::query()
            ->where('numero_orden', $numeroOrden)
            ->when($ubicacion, fn ($q) => $q->where('ubicacion', $ubicacion))
            ->when($fechaEntrada, fn ($q) => $q->whereDate('fecha_entrada', $fechaEntrada))
            ->exists();
    }
}
