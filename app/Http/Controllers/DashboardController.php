<?php

namespace App\Http\Controllers;

use App\Models\Ticket;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'nuevos' => Ticket::where('estado_workflow', Ticket::NUEVO)->count(),
            'resueltos' => Ticket::where('estado_workflow', Ticket::RESUELTO)->count(),
            'cerrados' => Ticket::where('estado_workflow', Ticket::CERRADO)->count(),
            'vencidos' => Ticket::query()
                ->whereNotNull('sla_vencimiento')
                ->where('sla_vencimiento', '<', now())
                ->where('estado_workflow', '!=', Ticket::CERRADO)
                ->count(),
            'por_vencer' => Ticket::query()
                ->whereNotNull('sla_vencimiento')
                ->whereBetween('sla_vencimiento', [now(), now()->addDay()])
                ->where('estado_workflow', '!=', Ticket::CERRADO)
                ->count(),
            'alertas_sin_leer' => \App\Models\AlertaSla::whereNull('leida_at')->count(),
        ];

        return view('dashboard', compact('stats'));
    }
}
