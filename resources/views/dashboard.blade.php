@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Hola, {{ auth()->user()->name }} 👋</h1>
        <p class="text-sm text-slate-500">Estado general del sistema de auditoría.</p>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="text-sm text-slate-500">Tickets nuevos</div>
            <div class="mt-1 text-3xl font-bold text-slate-900">{{ $stats['nuevos'] ?? 0 }}</div>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="text-sm text-slate-500">Resueltos (pendientes de cierre)</div>
            <div class="mt-1 text-3xl font-bold text-amber-600">{{ $stats['resueltos'] ?? 0 }}</div>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="text-sm text-slate-500">Cerrados</div>
            <div class="mt-1 text-3xl font-bold text-emerald-600">{{ $stats['cerrados'] ?? 0 }}</div>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <div class="text-sm text-slate-500">SLA vencido</div>
            <div class="mt-1 text-3xl font-bold text-red-600">{{ $stats['vencidos'] ?? 0 }}</div>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <a href="{{ route('tickets.index') }}?filtroSla=vencido"
           class="rounded-xl bg-red-50 p-5 shadow-sm ring-1 ring-red-200 hover:bg-red-100">
            <div class="text-sm font-medium text-red-700">⚠️ SLA vencido</div>
            <div class="mt-1 text-3xl font-bold text-red-700">{{ $stats['vencidos'] ?? 0 }}</div>
            <div class="mt-1 text-xs text-red-600">Ver tickets</div>
        </a>
        <a href="{{ route('tickets.index') }}?filtroSla=por_vencer"
           class="rounded-xl bg-amber-50 p-5 shadow-sm ring-1 ring-amber-200 hover:bg-amber-100">
            <div class="text-sm font-medium text-amber-700">⏳ Por vencer (24h)</div>
            <div class="mt-1 text-3xl font-bold text-amber-700">{{ $stats['por_vencer'] ?? 0 }}</div>
            <div class="mt-1 text-xs text-amber-600">Ver tickets</div>
        </a>
        <a href="{{ route('tickets.index') }}?filtroEstado=nuevo"
           class="rounded-xl bg-blue-50 p-5 shadow-sm ring-1 ring-blue-200 hover:bg-blue-100">
            <div class="text-sm font-medium text-blue-700">🆕 Nuevos</div>
            <div class="mt-1 text-3xl font-bold text-blue-700">{{ $stats['nuevos'] ?? 0 }}</div>
            <div class="mt-1 text-xs text-blue-600">Ver tickets</div>
        </a>
        <a href="{{ route('alertas.index') }}"
           class="rounded-xl bg-slate-50 p-5 shadow-sm ring-1 ring-slate-200 hover:bg-slate-100">
            <div class="text-sm font-medium text-slate-700">⏰ Alertas sin leer</div>
            <div class="mt-1 text-3xl font-bold text-slate-700">{{ $stats['alertas_sin_leer'] ?? 0 }}</div>
            <div class="mt-1 text-xs text-slate-600">Ver alertas</div>
        </a>
    </div>

    <div class="mt-6 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <h2 class="font-semibold text-slate-900">Próximos pasos</h2>
        <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-slate-600">
            <li>Importá el Excel de tickets (hojas de Formosa 2023).</li>
            <li>Asigná técnicos a cada ticket.</li>
            <li>Seguí el recorrido de la cuadrilla en el mapa en vivo.</li>
        </ul>
    </div>
@endsection
