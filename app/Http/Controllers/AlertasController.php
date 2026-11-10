<?php

namespace App\Http\Controllers;

use App\Models\AlertaSla;
use Illuminate\Http\Request;

class AlertasController extends Controller
{
    public function index(Request $request)
    {
        $tipo = $request->string('tipo')->toString();

        $alertas = AlertaSla::query()
            ->with('ticket')
            ->when($tipo !== '', fn ($q) => $q->where('tipo', $tipo))
            ->orderByRaw('leida_at IS NULL DESC')
            ->orderByDesc('created_at')
            ->paginate(30);

        $totales = [
            'por_vencer' => AlertaSla::where('tipo', AlertaSla::TIPO_POR_VENCER)->whereNull('leida_at')->count(),
            'vencido' => AlertaSla::where('tipo', AlertaSla::TIPO_VENCIDO)->whereNull('leida_at')->count(),
        ];

        return view('alertas.index', compact('alertas', 'tipo', 'totales'));
    }

    public function marcarLeida(Request $request, AlertaSla $alerta)
    {
        $alerta->update(['leida_at' => now()]);

        return back()->with('exito', 'Alerta marcada como leída.');
    }

    public function marcarTodasLeidas(Request $request)
    {
        AlertaSla::whereNull('leida_at')->update(['leida_at' => now()]);

        return back()->with('exito', 'Todas las alertas marcadas como leídas.');
    }
}
