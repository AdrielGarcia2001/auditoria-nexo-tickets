<?php

namespace App\Console\Commands;

use App\Models\AlertaSla;
use App\Models\Ticket;
use Illuminate\Console\Command;

class AlertarSla extends Command
{
    protected $signature = 'sla:alertar';

    protected $description = 'Crea las alertas SLA: tickets que vencen en 24h y tickets vencidos';

    public function handle(): int
    {
        // ====== por vencer: vence dentro de las proximas 24 horas ======
        $porVencer = Ticket::query()
            ->whereNotNull('sla_vencimiento')
            ->whereBetween('sla_vencimiento', [now(), now()->addDay()])
            ->where('estado_workflow', '!=', Ticket::CERRADO)
            ->whereDoesntHave('alertas', fn ($q) => $q->where('tipo', AlertaSla::TIPO_POR_VENCER))
            ->get();

        foreach ($porVencer as $ticket) {
            AlertaSla::create([
                'ticket_id' => $ticket->id,
                'tipo' => AlertaSla::TIPO_POR_VENCER,
                'mensaje' => sprintf(
                    'El ticket #%s vence el %s (%s).',
                    $ticket->numero_orden,
                    $ticket->sla_vencimiento->format('d/m/Y H:i'),
                    $ticket->prioridad ?? 'sin prioridad'
                ),
            ]);
        }

        // ====== vencidos: paso el sla y sigue sin cerrar ======
        $vencidos = Ticket::query()
            ->whereNotNull('sla_vencimiento')
            ->where('sla_vencimiento', '<', now())
            ->where('estado_workflow', '!=', Ticket::CERRADO)
            ->whereDoesntHave('alertas', fn ($q) => $q->where('tipo', AlertaSla::TIPO_VENCIDO))
            ->get();

        foreach ($vencidos as $ticket) {
            AlertaSla::create([
                'ticket_id' => $ticket->id,
                'tipo' => AlertaSla::TIPO_VENCIDO,
                'mensaje' => sprintf(
                    'VENCIDO: el ticket #%s venció el %s (%s).',
                    $ticket->numero_orden,
                    $ticket->sla_vencimiento->format('d/m/Y H:i'),
                    $ticket->prioridad ?? 'sin prioridad'
                ),
            ]);

            $ticket->sla_alertado_at = now();
            $ticket->saveQuietly();
        }

        $this->info(sprintf(
            'Alertas creadas: %d por vencer, %d vencidas.',
            $porVencer->count(),
            $vencidos->count()
        ));

        return self::SUCCESS;
    }
}
