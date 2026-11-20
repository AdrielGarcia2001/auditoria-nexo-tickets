<?php

namespace App\Http\Controllers;

use App\Models\ArticuloInventario;
use App\Models\ConsumibleTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Panel de CONTROL DE GASTO por mes (insumos consumidos).
 * Gráficos en porcentajes + comparación entre 2 o más meses.
 */
class GastoController extends Controller
{
    public function index(Request $request)
    {
        if (! $request->user()->isAdmin() && ! $request->user()->isSupervisor()) {
            abort(403);
        }

        // meses disponibles con consumo
        $mesesDisponibles = ConsumibleTicket::query()
            ->select(DB::raw("to_char(consumido_at, 'YYYY-MM') as mes"))
            ->groupBy(DB::raw("to_char(consumido_at, 'YYYY-MM')"))
            ->orderByDesc('mes')
            ->pluck('mes')
            ->all();

        // meses seleccionados (por defecto los ultimos 3)
        $mesesSel = $request->input('meses', []);
        if (! is_array($mesesSel) || empty($mesesSel)) {
            $mesesSel = array_slice($mesesDisponibles, 0, 3);
        }
        $mesesSel = array_values(array_intersect($mesesSel, $mesesDisponibles));

        // consulta base de consumibles en los meses seleccionados
        $query = ConsumibleTicket::query()
            ->with('articulo')
            ->when(! empty($mesesSel), function ($q) use ($mesesSel) {
                $q->whereIn(DB::raw("to_char(consumido_at, 'YYYY-MM')"), $mesesSel);
            });

        $consumibles = $query->get();

        // gasto total y total de material por mes
        $porMes = [];
        foreach ($mesesSel as $mes) {
            $filas = $consumibles->filter(fn ($c) => $c->consumido_at->format('Y-m') === $mes);
            $porMes[$mes] = [
                'gasto' => $filas->sum(fn ($c) => $c->gasto),
                'cantidad' => $filas->sum('cantidad_utilizada'),
                'registros' => $filas->count(),
            ];
        }

        $gastoTotalGeneral = $porMes ? array_sum(array_column($porMes, 'gasto')) : 0;

        // porcentaje de gasto de cada mes respecto al total del periodo
        foreach ($porMes as $mes => $datos) {
            $porMes[$mes]['porcentaje'] = $gastoTotalGeneral > 0
                ? round($datos['gasto'] / $gastoTotalGeneral * 100, 1)
                : 0;
        }

        // distribucion por articulo (en todo el periodo seleccionado) en %
        $porArticulo = [];
        foreach ($consumibles as $c) {
            $id = $c->articulo_id;
            if (! isset($porArticulo[$id])) {
                $porArticulo[$id] = [
                    'nombre' => $c->articulo?->nombre_articulo ?? '—',
                    'cantidad' => 0,
                    'gasto' => 0,
                ];
            }
            $porArticulo[$id]['cantidad'] += $c->cantidad_utilizada;
            $porArticulo[$id]['gasto'] += $c->gasto;
        }
        foreach ($porArticulo as $id => $datos) {
            $porArticulo[$id]['porcentaje'] = $gastoTotalGeneral > 0
                ? round($datos['gasto'] / $gastoTotalGeneral * 100, 1)
                : 0;
        }
        usort($porArticulo, fn ($a, $b) => $b['gasto'] <=> $a['gasto']);

        // articulos del inventario (para dar de alta consumo)
        $articulos = ArticuloInventario::where('activo', true)->orderBy('nombre_articulo')->get();

        // datos para los graficos de torta (Chart.js)
        $dataGraficos = [
            'meses' => [
                'etiquetas' => array_map(
                    fn ($mes) => \Carbon\Carbon::createFromFormat('Y-m', $mes)->translatedFormat('M Y'),
                    array_keys($porMes)
                ),
                'valores' => array_values(array_column($porMes, 'gasto')),
            ],
            'insumos' => [
                'etiquetas' => array_column($porArticulo, 'nombre'),
                'valores' => array_column($porArticulo, 'gasto'),
            ],
        ];

        return view('gastos.index', compact(
            'mesesDisponibles',
            'mesesSel',
            'porMes',
            'gastoTotalGeneral',
            'porArticulo',
            'articulos',
            'dataGraficos'
        ));
    }
}
