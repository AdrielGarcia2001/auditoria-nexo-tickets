<?php

namespace App\Http\Controllers;

use App\Models\AlertaSla;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $usuario = Auth::user();

        $stats = [
            'nuevos' => Ticket::porRol($usuario)->where('estado_workflow', Ticket::NUEVO)->count(),
            'resueltos' => Ticket::porRol($usuario)->where('estado_workflow', Ticket::RESUELTO)->count(),
            'cerrados' => Ticket::porRol($usuario)->where('estado_workflow', Ticket::CERRADO)->count(),
            'vencidos' => Ticket::porRol($usuario)
                ->whereNotNull('sla_vencimiento')
                ->where('sla_vencimiento', '<', now())
                ->where('estado_workflow', '!=', Ticket::CERRADO)
                ->count(),
            'por_vencer' => Ticket::porRol($usuario)
                ->whereNotNull('sla_vencimiento')
                ->whereBetween('sla_vencimiento', [now(), now()->addDay()])
                ->where('estado_workflow', '!=', Ticket::CERRADO)
                ->count(),
            'alertas_sin_leer' => AlertaSla::whereNull('leida_at')
                ->when($usuario->isSupervisor(), fn ($q) => $q->whereHas('ticket.sitio.zona', fn ($z) => $z->whereIn('id', $usuario->zonaIds())))
                ->when($usuario->isTecnico(), fn ($q) => $q->whereHas('ticket.tecnicos', fn ($t) => $t->whereKey($usuario->id)))
                ->count(),
        ];

        // Info extra solo para admin (info con datos del sistema).
        $infoAdmin = null;
        if ($usuario->isAdmin()) {
            $infoAdmin = [
                'total_usuarios' => User::count(),
                'total_supervisores' => User::where('role', User::ROLE_SUPERVISOR)->count(),
                'total_tecnicos' => User::where('role', User::ROLE_TECNICO)->count(),
                'total_zonas' => \App\Models\Zona::where('activo', true)->count(),
                'total_sitios' => \App\Models\Sitio::count(),
            ];
        }

        return view('dashboard', compact('stats', 'infoAdmin', 'usuario'));
    }
}
