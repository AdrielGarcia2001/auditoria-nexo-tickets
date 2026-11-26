<?php

namespace App\Livewire;

use App\Models\ImportacionExcel;
use App\Models\Ticket;
use App\Services\Importacion\ImportadorTickets;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Importador de tickets desde Excel (.xlsx) o export de Google Sheets.
 * El mismo archivo no se puede subir dos veces (hash).
 * Tickets nuevos se insertan; tickets con cambios se actualizan.
 */
class ImportadorExcel extends Component
{
    use WithFileUploads;

    public ?\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $archivo = null;

    public ?string $hashArchivo = null;

    /** @var array<string, int> hojas del archivo => filas */
    public array $hojas = [];

    public ?string $hojaSeleccionada = null;

    /** @var array<int, string> encabezados tal cual el Excel */
    public array $columnas = [];

    /** @var array<int, array> primeras filas para el preview */
    public array $filas = [];

    public int $totalFilas = 0;
    public int $filasMostradas = 0;
    public int $ignoradas = 0;
    public int $duplicados = 0;
    public int $nuevos = 0;
    public int $modificados = 0;
    public int $sinCambios = 0;

    public ?string $tokenPreview = null;

    /** @var array{importados:int, fallidos:array<int,string>}|null */
    public ?array $resultado = null;

    public bool $importando = false;

    private const MAX_PREVIEW = 100;

    protected function rules(): array
    {
        return ['archivo' => ['required', 'file', 'mimes:xlsx,csv', 'max:102400']];
    }

    protected function messages(): array
    {
        return [
            'archivo.required' => 'Elegí un archivo .xlsx.',
            'archivo.mimes' => 'Solo se aceptan archivos .xlsx o .csv.',
            'archivo.max' => 'El archivo supera el máximo permitido (100 MB).',
        ];
    }

    public function updatedArchivo(): void
    {
        $this->validate();

        $ruta = $this->archivo->getRealPath();

        $this->hashArchivo = hash_file('sha256', $ruta);

        $yaImportado = ImportacionExcel::yaImportado($this->hashArchivo);
        if ($yaImportado) {
            $this->addError('archivo', 'Este archivo ya se importó anteriormente ('.$yaImportado->importado_at?->format('d/m/Y H:i').'). No se puede subir dos veces.');
            $this->resetArchivo();
            return;
        }

        try {
            $this->hojas = $this->importador()->hojas($ruta);
        } catch (\Throwable $e) {
            $this->addError('archivo', 'No se pudo leer el archivo: '.$e->getMessage());
            return;
        }

        $this->hojaSeleccionada = collect(ImportadorTickets::HOJAS_TICKETS)
            ->first(fn ($h) => isset($this->hojas[$h]))
            ?? array_key_first($this->hojas);

        $this->resultado = null;
        $this->prepararPreview($ruta);
    }

    public function seleccionarHoja(string $hoja): void
    {
        if (! isset($this->hojas[$hoja])) {
            return;
        }

        $this->hojaSeleccionada = $hoja;
        $this->resultado = null;

        $ruta = $this->archivo?->getRealPath();

        if (! $ruta || ! is_file($ruta)) {
            $this->resetArchivo();
            $this->addError('archivo', 'El archivo ya no está disponible, subilo de nuevo.');
            return;
        }

        $this->prepararPreview($ruta);
    }

    public function importar(): void
    {
        if (! $this->hojaSeleccionada || ! $this->tokenPreview || ! $this->hashArchivo) {
            return;
        }

        $rutaDatos = $this->rutaDatos();

        if (! is_file($rutaDatos)) {
            $this->addError('archivo', 'La vista previa expiró. Subí el archivo de nuevo.');
            return;
        }

        $this->importando = true;

        try {
            $datos = json_decode((string) file_get_contents($rutaDatos), true) ?: [];
            $filas = $datos['filas'] ?? [];

            // clasificar: nuevos | modificados (con cambios) | sin cambios
            $nuevos = [];
            $modificados = [];
            $sinCambios = 0;

            foreach ($filas as $fila) {
                $numeroOrden = $fila['numero_orden'] ?? '';
                $ubicacion = $fila['ubicacion'] ?? '';
                $fechaEntrada = $fila['fecha_entrada'] ?? null;

                $existe = Ticket::query()
                    ->where('numero_orden', $numeroOrden)
                    ->when($ubicacion, fn ($q) => $q->where('ubicacion', $ubicacion))
                    ->when($fechaEntrada, fn ($q) => $q->whereDate('fecha_entrada', $fechaEntrada))
                    ->first();

                if (! $existe) {
                    $nuevos[] = $fila;
                } elseif ($this->importador()->esIgual($existe, $fila)) {
                    $sinCambios++;
                } else {
                    $modificados[] = ['ticket' => $existe, 'fila' => $fila];
                }
            }

            $this->nuevos = count($nuevos);
            $this->modificados = count($modificados);
            $this->sinCambios = $sinCambios;

            $resultado = $this->importador()->importar(
                $nuevos,
                auth()->user(),
                $this->hojaSeleccionada
            );

            $cambiosAplicados = 0;
            foreach ($modificados as $item) {
                if ($this->importador()->aplicarCambios(
                    $item['ticket'],
                    $item['fila'],
                    auth()->user(),
                    $this->hojaSeleccionada
                )) {
                    $cambiosAplicados++;
                }
            }

            $resultado['modificados'] = $cambiosAplicados;
            $resultado['sin_cambios'] = $sinCambios;

            ImportacionExcel::create([
                'nombre_archivo' => $this->archivo?->getClientOriginalName() ?? 'archivo.xlsx',
                'hash_archivo' => $this->hashArchivo,
                'usuario_id' => auth()->id(),
                'total_filas' => count($filas),
                'importados' => $resultado['importados'],
                'importado_at' => now(),
            ]);

            $this->resultado = $resultado;
            unset($resultado);

            @unlink($rutaDatos);
        } catch (\Throwable $e) {
            report($e);
            $this->addError('archivo', 'Error al importar: '.$e->getMessage());
        } finally {
            $this->importando = false;
        }
    }

    public function reiniciar(): void
    {
        if ($this->tokenPreview) {
            @unlink($this->rutaDatos());
        }

        $this->reset(['archivo', 'hojas', 'hojaSeleccionada', 'columnas', 'filas',
            'totalFilas', 'ignoradas', 'duplicados', 'tokenPreview', 'resultado',
            'nuevos', 'modificados', 'sinCambios', 'hashArchivo']);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.importador-excel');
    }

    /** Devuelve el campo del sistema correspondiente a un encabezado del Excel. */
    public function campoDe(string $columna): string
    {
        $normalizada = preg_replace('/\s+/u', ' ', mb_strtolower(trim(str_replace('°', '', $columna)), 'UTF-8'));
        $mapa = [
            'n orden' => 'numero_orden', 'orden' => 'numero_orden',
            'clase orden' => 'clase_orden', 'fecha entrada' => 'fecha_entrada',
            'grupo planificador' => 'grupo_planificador', 'texto breve' => 'texto_breve',
            'ubicacion' => 'ubicacion', 'prioridad' => 'prioridad', 'estado' => 'estado',
        ];

        return $mapa[$normalizada] ?? $normalizada;
    }

    // ====== internos ======

    private function importador(): ImportadorTickets
    {
        return app(ImportadorTickets::class);
    }

    private function prepararPreview(string $ruta): void
    {
        try {
            $parseo = $this->importador()->parsear($ruta, (string) $this->hojaSeleccionada);
        } catch (\Throwable $e) {
            $this->addError('archivo', $e->getMessage());
            return;
        }

        $this->columnas = $parseo['columnas'];
        $this->totalFilas = count($parseo['filas']);
        $this->ignoradas = $parseo['ignoradas'];
        $this->duplicados = collect($parseo['filas'])->where('_duplicado', true)->count();
        $this->filas = array_slice($parseo['filas'], 0, self::MAX_PREVIEW);
        $this->filasMostradas = count($this->filas);

        $this->tokenPreview = $this->tokenPreview ?: bin2hex(random_bytes(16));
        $directorio = storage_path('app/imports');
        if (! is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }
        file_put_contents(
            $this->rutaDatos(),
            json_encode(['filas' => $parseo['filas'], 'hoja' => $this->hojaSeleccionada], JSON_UNESCAPED_UNICODE)
        );

        $this->limpiarPreviewsViejos();
    }

    private function rutaDatos(): string
    {
        return storage_path('app/imports/'.$this->tokenPreview.'.json');
    }

    private function resetArchivo(): void
    {
        $this->reset(['archivo', 'hojas', 'hojaSeleccionada', 'columnas', 'filas',
            'totalFilas', 'ignoradas', 'duplicados', 'tokenPreview',
            'nuevos', 'modificados', 'sinCambios', 'hashArchivo']);
        $this->resetValidation();
    }

    /** Borra previews de mas de 2 horas (higiene). */
    private function limpiarPreviewsViejos(): void
    {
        foreach (glob(storage_path('app/imports/*.json')) ?: [] as $archivo) {
            if (filemtime($archivo) < now()->subHours(2)->getTimestamp()) {
                @unlink($archivo);
            }
        }
    }
}
