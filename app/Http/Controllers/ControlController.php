<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Zona;
use Illuminate\Http\Request;

/**
 * Panel de CONTROL del administrador: estados y progreso de los tickets
 * cargados. Solo lectura (para supervisar, sin modificar).
 */
class ControlController extends Controller
{
    public function __invoke(Request $request)
    {
        // Solo administrador
        if (! $request->user()->isAdmin()) {
            abort(403, 'Este panel es solo para el administrador.');
        }

        $total = Ticket::count();

        // Estados del workflow (con porcentaje)
        $porEstado = [
            Ticket::NUEVO => Ticket::where('estado_workflow', Ticket::NUEVO)->count(),
            Ticket::RESUELTO => Ticket::where('estado_workflow', Ticket::RESUELTO)->count(),
            Ticket::CERRADO => Ticket::where('estado_workflow', Ticket::CERRADO)->count(),
        ];
        foreach ($porEstado as $estado => $cantidad) {
            $porEstado[$estado] = [
                'cantidad' => $cantidad,
                'porcentaje' => $total > 0 ? round($cantidad / $total * 100, 1) : 0,
            ];
        }

        // Progreso por tipo (correctivo / preventivo)
        $porTipo = [
            'correctivo' => Ticket::where('tipo', 'correctivo')->count(),
            'preventivo' => Ticket::where('tipo', 'preventivo')->count(),
        ];
        foreach ($porTipo as $tipo => $cantidad) {
            $porTipo[$tipo] = [
                'cantidad' => $cantidad,
                'porcentaje' => $total > 0 ? round($cantidad / $total * 100, 1) : 0,
            ];
        }

        // Progreso por zona
        $porZona = Zona::where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->map(function ($zona) {
                $cantidad = Ticket::whereHas('sitio', fn ($s) => $s->where('zona_id', $zona->id))->count();
                return [
                    'nombre' => $zona->nombre,
                    'cantidad' => $cantidad,
                    'cerrados' => Ticket::whereHas('sitio', fn ($s) => $s->where('zona_id', $zona->id))
                        ->where('estado_workflow', Ticket::CERRADO)->count(),
                ];
            });

        // Progreso por supervisor (sus técnicos / zonas)
        $porSupervisor = User::where('role', User::ROLE_SUPERVISOR)
            ->where('activo', true)
            ->orderBy('name')
            ->get()
            ->map(function ($sup) {
                $zonaIds = $sup->zonaIds();
                return [
                    'nombre' => $sup->name,
                    'tecnicos' => count($sup->tecnicoIds()),
                    'zonas' => count($zonaIds),
                    'tickets' => Ticket::whereHas('sitio.zona', fn ($z) => $z->whereIn('id', $zonaIds))->count(),
                ];
            });

        // Tickets recientes con su estado (para control)
        $recientes = Ticket::with(['sitio', 'tecnicos'])
            ->latest('fecha_entrada')
            ->limit(15)
            ->get(['id', 'numero_orden', 'texto_breve', 'estado_workflow', 'prioridad', 'fecha_entrada', 'sitio_id']);

        return view('control.index', compact(
            'total',
            'porEstado',
            'porTipo',
            'porZona',
            'porSupervisor',
            'recientes'
        ));
    }
}
