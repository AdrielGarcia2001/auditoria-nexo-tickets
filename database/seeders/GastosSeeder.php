<?php

namespace Database\Seeders;

use App\Models\ArticuloInventario;
use App\Models\ConsumibleTicket;
use App\Models\Ticket;
use Illuminate\Database\Seeder;

class GastosSeeder extends Seeder
{
    public function run(): void
    {
        $articulos = [
            ['nombre_articulo' => 'Focos LED 40W', 'codigo' => 'FOC-40', 'precio_unitario' => 12500, 'stock_actual' => 50, 'stock_minimo' => 10],
            ['nombre_articulo' => 'Cable 3x1.5 mm', 'codigo' => 'CAB-315', 'precio_unitario' => 8200, 'stock_actual' => 200, 'stock_minimo' => 50],
            ['nombre_articulo' => 'Tornillería surtida', 'codigo' => 'TOR-SUR', 'precio_unitario' => 3500, 'stock_actual' => 300, 'stock_minimo' => 80],
            ['nombre_articulo' => 'Aislante termico', 'codigo' => 'AIS-TER', 'precio_unitario' => 24000, 'stock_actual' => 30, 'stock_minimo' => 8],
            ['nombre_articulo' => 'Guantes de seguridad', 'codigo' => 'GUA-SEG', 'precio_unitario' => 4500, 'stock_actual' => 100, 'stock_minimo' => 20],
        ];

        $guardados = [];
        foreach ($articulos as $a) {
            $guardados[] = ArticuloInventario::updateOrCreate(
                ['codigo' => $a['codigo']],
                $a
            );
        }

        // ticket de ejemplo si no hay ninguno
        $ticket = Ticket::first();
        if (! $ticket) {
            $ticket = Ticket::create([
                'numero_orden' => 'DEMO-GASTOS',
                'clase_orden' => 'FSMA',
                'fecha_entrada' => now()->toDateString(),
                'prioridad' => '48 horas',
                'texto_breve' => 'Ticket demo para control de gastos',
                'estado_workflow' => Ticket::NUEVO,
            ]);
        }

        $meses = [now()->subMonths(2), now()->subMonth(), now()];
        foreach ($meses as $index => $mes) {
            foreach ($guardados as $j => $articulo) {
                $cantidad = (($index + 1) * ($j + 1)) % 5 + 1;
                ConsumibleTicket::firstOrCreate(
                    [
                        'ticket_id' => $ticket->id,
                        'articulo_id' => $articulo->id,
                        'consumido_at' => $mes->copy()->startOfMonth()->addDays(5),
                    ],
                    [
                        'cantidad_utilizada' => $cantidad,
                        'precio_unitario_momento' => $articulo->precio_unitario,
                        'registrado_por' => 1,
                    ]
                );
            }
        }
    }
}
