@extends('layouts.app')

@section('title', 'Control de gasto')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">💰 Control de gasto por mes</h1>
        <p class="text-sm text-slate-500">
            Comparación de gastos de pedidos y total de material consumido por mes, en porcentajes.
        </p>
    </div>

    {{-- FILTRO DE MESES --}}
    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <form method="GET" action="{{ route('gastos.index') }}" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[240px]">
                <label class="mb-1 block text-sm font-medium text-slate-700">Meses a comparar</label>
                <div class="flex flex-wrap gap-2">
                    @forelse ($mesesDisponibles as $mes)
                        <label class="flex items-center gap-1.5 rounded-lg bg-slate-50 px-3 py-1.5 text-sm ring-1 ring-slate-200">
                            <input type="checkbox" name="meses[]" value="{{ $mes }}"
                                   {{ in_array($mes, $mesesSel) ? 'checked' : '' }}
                                   class="rounded border-slate-300">
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $mes)->translatedFormat('M Y') }}
                        </label>
                    @empty
                        <span class="text-sm text-slate-400">Todavía no hay consumo registrado.</span>
                    @endforelse
                </div>
            </div>
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                Comparar
            </button>
        </form>
    </div>

    {{-- RESUMEN --}}
    <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="text-sm text-slate-500">Gasto total del período</div>
            <div class="mt-1 text-2xl font-bold text-slate-900">$ {{ number_format($gastoTotalGeneral, 2, ',', '.') }}</div>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="text-sm text-slate-500">Meses comparados</div>
            <div class="mt-1 text-2xl font-bold text-slate-900">{{ count($mesesSel) }}</div>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="text-sm text-slate-500">Material consumido</div>
            <div class="mt-1 text-2xl font-bold text-slate-900">
                {{ number_format(array_sum(array_column($porMes, 'cantidad')), 0, ',', '.') }} unid.
            </div>
        </div>
    </div>

    {{-- SELECTOR DE TIPO DE GRAFICO --}}
    <div class="mt-5 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
        <div>
            <h2 class="font-semibold text-slate-900">📊 Visualización</h2>
            <p class="text-xs text-slate-500">Elegí el tipo de gráfico para ver la distribución del gasto.</p>
        </div>
        <div class="inline-flex rounded-lg bg-slate-100 p-1 ring-1 ring-slate-200">
            <button type="button" id="btn-torta"
                    onclick="cambiarTipoGrafico('torta')"
                    class="rounded-md px-4 py-2 text-sm font-semibold transition bg-slate-900 text-white shadow-sm">
                🍩 Torta
            </button>
            <button type="button" id="btn-barra"
                    onclick="cambiarTipoGrafico('barra')"
                    class="rounded-md px-4 py-2 text-sm font-semibold transition text-slate-600 hover:text-slate-900">
                📊 Barras
            </button>
            <button type="button" id="btn-lineal"
                    onclick="cambiarTipoGrafico('lineal')"
                    class="rounded-md px-4 py-2 text-sm font-semibold transition text-slate-600 hover:text-slate-900">
                📈 Lineal
            </button>
        </div>
        <select id="tipo-grafico" class="hidden">
            <option value="torta">torta</option>
            <option value="barra">barra</option>
            <option value="lineal">lineal</option>
        </select>
    </div>

    {{-- GRAFICOS (Chart.js: torta / barras) --}}
    <div id="grafico-chart" class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="font-semibold text-slate-900">🍩 Gasto por mes</h2>
            <p class="text-xs text-slate-500">Distribución porcentual del gasto entre los meses comparados.</p>
            <div class="relative mt-3 h-64">
                <canvas id="chart-gasto-mes"></canvas>
            </div>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="font-semibold text-slate-900">🍩 Gasto por insumo</h2>
            <p class="text-xs text-slate-500">Distribución porcentual del gasto entre los insumos.</p>
            <div class="relative mt-3 h-64">
                <canvas id="chart-gasto-insumo"></canvas>
            </div>
        </div>
    </div>

    {{-- datos para Chart.js --}}
    <script id="datos-gastos" type="application/json">
        @json($dataGraficos)
    </script>

    {{-- ============================================================
         GRAFICOS LINEALES (barras horizontales de progreso)
    ============================================================ --}}
    <div id="grafico-lineal" style="display:none;">

    {{-- GASTO POR MES (barras en %) --}}
    <div class="mt-5 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="font-semibold text-slate-900">📈 Gasto por mes (%)</h2>
        @if ($porMes)
            <div class="mt-4 space-y-3">
                @foreach ($porMes as $mes => $datos)
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium text-slate-700">
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $mes)->translatedFormat('F Y') }}
                            </span>
                            <span class="text-slate-500">
                                $ {{ number_format($datos['gasto'], 2, ',', '.') }} · {{ $datos['porcentaje'] }}%
                            </span>
                        </div>
                        @php($coloresMes = ['#2563eb', '#7c3aed', '#0891b2', '#db2777', '#ea580c'])
                        <div class="mt-1 w-full overflow-hidden"
                             style="background-color: #e5e7eb; border-radius: 9999px; height: 12px;">
                            <div style="height: 100%; border-radius: 9999px; background-color: {{ $coloresMes[$loop->index % count($coloresMes)] }}; width: {{ $datos['porcentaje'] }}%;"></div>
                        </div>
                        <div class="mt-0.5 text-xs text-slate-400">
                            {{ number_format($datos['cantidad'], 0, ',', '.') }} unidades de material
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="mt-3 text-sm text-slate-400">No hay meses con consumo para comparar.</p>
        @endif
    </div>

    {{-- DISTRIBUCION POR INSUMO (%) --}}
    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="font-semibold text-slate-900">🧾 Gasto por insumo (%)</h2>
            @if ($porArticulo)
                <div class="mt-4 space-y-3">
                    @foreach ($porArticulo as $articulo)
                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-700">{{ $articulo['nombre'] }}</span>
                                <span class="text-slate-500">{{ $articulo['porcentaje'] }}%</span>
                            </div>
                            @php($coloresInsumo = ['#2563eb', '#16a34a', '#d97706', '#dc2626', '#7c3aed'])
                            <div class="mt-1 w-full overflow-hidden"
                                 style="background-color: #e5e7eb; border-radius: 9999px; height: 10px;">
                                <div style="height: 100%; border-radius: 9999px; background-color: {{ $coloresInsumo[$loop->index % count($coloresInsumo)] }}; width: {{ $articulo['porcentaje'] }}%;"></div>
                            </div>
                            <div class="mt-0.5 text-xs text-slate-400">
                                {{ number_format($articulo['cantidad'], 0, ',', '.') }} unid. · $ {{ number_format($articulo['gasto'], 2, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-3 text-sm text-slate-400">Sin insumos consumidos en el período.</p>
            @endif
    </div>

    </div>{{-- /grafico-lineal --}}

    {{-- COMPARATIVA TABLA (siempre visible) --}}
    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="font-semibold text-slate-900">📊 Comparativa entre meses</h2>
            <div class="mt-3 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-xs uppercase text-slate-400">
                        <tr>
                            <th class="py-2 text-left">Mes</th>
                            <th class="py-2 text-right">Gasto</th>
                            <th class="py-2 text-right">Material</th>
                            <th class="py-2 text-right">%</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($porMes as $mes => $datos)
                            <tr>
                                <td class="py-2 font-medium text-slate-700">
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $mes)->translatedFormat('M Y') }}
                                </td>
                                <td class="py-2 text-right text-slate-600">$ {{ number_format($datos['gasto'], 2, ',', '.') }}</td>
                                <td class="py-2 text-right text-slate-600">{{ number_format($datos['cantidad'], 0, ',', '.') }}</td>
                                <td class="py-2 text-right text-slate-500">{{ $datos['porcentaje'] }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-3 text-slate-400">Sin datos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
    </div>
@endsection
