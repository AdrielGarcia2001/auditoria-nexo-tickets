<?php

namespace App\Livewire;

use App\Services\Importacion\ImportadorTickets;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Importador de tickets desde Excel (.xlsx) o export de Google Sheets.
 *
 * 1) sube archivo  2) elegís la hoja  3) ves TODO el contenido (preview)
 * 4) importa: cada fila se inserta como ticket NUEVO (nunca actualiza)
 */
class ImportadorExcel extends Component
{
    use WithFileUploads;

    public ?\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $archivo = null;

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

    public ?string $tokenPreview = null;

    /** @var array{importados:int, fallidos:array<int,string>}|null */
    public ?array $resultado = null;

    public bool $importando = false;

    private const MAX_PREVIEW = 100;

    protected function rules(): array
    {
        return [
            'archivo' => ['required', 'file', 'mimes:xlsx,csv', 'max:102400'],
        ];
    }

    protected function messages(): array
    {
        return [
            'archivo.required' => 'Elegí un archivo .xlsx o el export de Google Sheets (.xlsx).',
            'archivo.mimes' => 'Solo se aceptan archivos .xlsx o .csv.',
            'archivo.max' => 'El archivo supera el máximo permitido (100 MB).',
        ];
    }

    // ====== eventos ======

    public function updatedArchivo(): void
    {
        $this->validate();

        $ruta = $this->archivo->getRealPath();

        try {
            $this->hojas = $this->importador()->hojas($ruta);
        } catch (\Throwable $e) {
            $this->addError('archivo', 'No se pudo leer el archivo: '.$e->getMessage());

            return;
        }

        // elige la primera hoja de tickets conocida
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

        // re-parsea desde el archivo original subido (si sigue disponible)
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
        if (! $this->hojaSeleccionada || ! $this->tokenPreview) {
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

            $resultado = $this->importador()->importar(
                $filas,
                auth()->user(),
                $this->hojaSeleccionada
            );

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
            'totalFilas', 'ignoradas', 'duplicados', 'tokenPreview', 'resultado']);
        $this->resetValidation();
    }

    // ====== render ======

    public function render()
    {
        return view('livewire.importador-excel');
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

        // guarda el parseo completo para importar despues (sin re-subir)
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
            'totalFilas', 'ignoradas', 'duplicados', 'tokenPreview']);
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
