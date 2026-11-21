@extends('layouts.app')

@section('title', 'Panel de control')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">🎛️ Panel de control</h1>
        <p class="text-sm text-slate-500">Estado y progreso de los tickets cargados. Solo lectura, para control del administrador.</p>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <div class="text-sm text-slate-500">Total de tickets cargados</div>
        <div class="mt-1 text-4xl font-bold text-slate-900">{{ number_format($total, 0, ',', '.') }}</div>
    </div>

    <div class="mt-5 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="font-semibold text-slate-900">📊 Estados de los tickets</h2>
        <div class="mt-4 space-y-3">
            @foreach ($porEstado as $estado => $datos)
                @php
                    $colores = [\App\Models\Ticket::NUEVO => 'bg-blue-500', \App\Models\Ticket::RESUELTO => 'bg-amber-500', \App\Models\Ticket::CERRADO => 'bg-emerald-500'];
                    $color = $colores[$estado] ?? 'bg-slate-500';
                @endphp
                <div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium capitalize text-slate-700">{{ $estado }}</span>
                        <span class="text-slate-500">{{ number_format($datos['cantidad'], 0, ',', '.') }} · {{ $datos['porcentaje'] }}%</span>
                    </div>
                    <div class="mt-1 h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full {{ $color }}" style="width: {{ $datos['porcentaje'] }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="font-semibold text-slate-900">🔧 Por tipo de orden</h2>
            <div class="mt-4 space-y-3">
                @foreach ($porTipo as $tipo => $datos)
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium capitalize text-slate-700">{{ $tipo }}</span>
                            <span class="text-slate-500">{{ number_format($datos['cantidad'], 0, ',', '.') }} · {{ $datos['porcentaje'] }}%</span>
                        </div>
                        <div class="mt-1 h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                            <div class="h-full rounded-full {{ $tipo === 'correctivo' ? 'bg-orange-500' : 'bg-blue-500' }}" style="width: {{ $datos['porcentaje'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="font-semibold text-slate-900">🗺️ Progreso por zona</h2>
            <div class="mt-3 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="text-xs uppercase text-slate-400">
                        <tr><th class="py-2 text-left">Zona</th><th class="py-2 text-right">Tickets</th><th class="py-2 text-right">Cerrados</th><th class="py-2 text-right">Progreso</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($porZona as $zona)
                            @php($pct = $zona['cantidad'] > 0 ? round($zona['cerrados'] / $zona['cantidad'] * 100) : 0)
                            <tr>
                                <td class="py-2 font-medium text-slate-700">{{ $zona['nombre'] }}</td>
                                <td class="py-2 text-right text-slate-600">{{ $zona['cantidad'] }}</td>
                                <td class="py-2 text-right text-emerald-600">{{ $zona['cerrados'] }}</td>
                                <td class="py-2 text-right text-slate-500">{{ $pct }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-3 text-slate-400">No hay zonas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-5 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="font-semibold text-slate-900">👔 Control por supervisor</h2>
        <div class="mt-3 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr><th class="py-2 text-left">Supervisor</th><th class="py-2 text-right">Técnicos</th><th class="py-2 text-right">Zonas</th><th class="py-2 text-right">Tickets</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($porSupervisor as $sup)
                        <tr>
                            <td class="py-2 font-medium text-slate-700">{{ $sup['nombre'] }}</td>
                            <td class="py-2 text-right text-slate-600">{{ $sup['tecnicos'] }}</td>
                            <td class="py-2 text-right text-slate-600">{{ $sup['zonas'] }}</td>
                            <td class="py-2 text-right text-slate-600">{{ $sup['tickets'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-3 text-slate-400">No hay supervisores.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-5 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="font-semibold text-slate-900">🕘 Últimos tickets cargados</h2>
            <p class="text-xs text-slate-500">Estado actual de progreso de cada ticket.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                    <tr><th class="px-4 py-3">N° Orden</th><th class="px-4 py-3">Asunto</th><th class="px-4 py-3">Sitio</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3">Prioridad</th><th class="px-4 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recientes as $ticket)
                        <tr class="hover:bg-slate-50">
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-slate-700">{{ $ticket->numero_orden }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $ticket->texto_breve }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $ticket->sitio?->nombre ?? '—' }}</td>
                            <td class="px-4 py-3">@include('partials.badge-estado', ['estado' => $ticket->estado_workflow])</td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ $ticket->prioridad }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right"><a href="{{ route('tickets.show', $ticket) }}" class="text-xs font-semibold text-blue-600 hover:underline">Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">No hay tickets todavía.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
