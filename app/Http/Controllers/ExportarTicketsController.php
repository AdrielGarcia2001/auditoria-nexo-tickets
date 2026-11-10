<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use OpenSpout\Common\Entity\Row;

/**
 * Exporta tickets a .xlsx (para que el supervisor descargue y re-suba).
 */
class ExportarTicketsController extends Controller
{
    public function __invoke(Request $request)
    {
        $usuario = $request->user();

        $busqueda = $request->input('busqueda', $request->input('filtroBusqueda', ''));
        $estado = $request->input('estado', $request->input('filtroEstado', ''));
        $prioridad = $request->input('prioridad', $request->input('filtroPrioridad', ''));
        $area = $request->input('area', $request->input('filtroArea', ''));

        $query = Ticket::query()
            ->with(['sitio', 'tecnicos'])
            ->when($usuario->isTecnico(), fn ($q) => $q->delTecnico($usuario))
            ->when($busqueda !== '', function ($q) use ($busqueda) {
                $t = '%'.$busqueda.'%';
                $q->where(function ($q2) use ($t) {
                    $q2->where('numero_orden', 'like', $t)
                        ->orWhere('texto_breve', 'like', $t)
                        ->orWhere('ubicacion', 'like', $t)
                        ->orWhere('calle', 'like', $t)
                        ->orWhere('lo_resolvio', 'like', $t);
                });
            })
            ->when($estado !== '', fn ($q) => $q->where('estado_workflow', $estado))
            ->when($prioridad !== '', fn ($q) => $q->where('prioridad', $prioridad))
            ->when($area !== '', fn ($q) => $q->whereRaw(
                "CASE WHEN origen_hoja = 'LIMP (M01PR012)' OR grupo_planificador = 'M02' THEN 'limpieza' ELSE 'mantenimiento' END = ?",
                [$area]
            ))
            ->orderByDesc('fecha_entrada');

        $directorio = storage_path('app/exports');
        if (! is_dir($directorio)) {
            mkdir($directorio, 0755, true);
        }
        $archivo = $directorio.'/tickets_'.uniqid().'.xlsx';

        $opciones = new \OpenSpout\Writer\XLSX\Options(new \OpenSpout\Common\Entity\Style\Style(), true, null, null, storage_path('app/exports'));

        $writer = new XlsxWriter($opciones);
        $writer->openToFile($archivo);

        // encabezados
        $writer->addRow(Row::fromValues([
            'N° Orden', 'Clase', 'Tipo', 'Estado workflow', 'Texto breve', 'Ubicación', 'Calle',
            'Provincia', 'Prioridad', 'Área', 'Estado (Excel)', 'Estatus', 'Lo resolvió',
            'Fecha entrada', 'Fecha resolución', 'SLA vencimiento', 'Técnicos', 'Origen hoja',
        ]));

        foreach ($query->cursor() as $t) {
            $writer->addRow(Row::fromValues([
                $t->numero_orden,
                $t->clase_orden,
                $t->tipo,
                $t->estado_workflow,
                $t->texto_breve,
                $t->ubicacion,
                $t->calle,
                $t->provincia_nombre,
                $t->prioridad,
                $t->area(),
                $t->estado,
                $t->estatus,
                $t->lo_resolvio,
                $t->fecha_entrada?->format('Y-m-d'),
                $t->fecha_resolucion?->format('Y-m-d'),
                $t->sla_vencimiento?->format('Y-m-d H:i'),
                $t->tecnicos->pluck('name')->implode(', '),
                $t->origen_hoja,
            ]));
        }

        $writer->close();

        return response()->download($archivo, 'tickets-exportados.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
