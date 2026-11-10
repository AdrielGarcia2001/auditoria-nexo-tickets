@extends('layouts.app')

@section('title', 'Tickets')

@section('content')
    <div class="mb-5 flex items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Tickets</h1>
            <p class="text-sm text-slate-500">Todos los tickets importados del Excel de Formosa.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('tickets.exportar') }}"
               class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-300 hover:bg-emerald-50">
                📤 Exportar Excel
            </a>
            @if (auth()->user()->puedeGestionar())
                <a href="{{ route('importar.index') }}"
                   class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                    📥 Importar Excel
                </a>
            @endif
        </div>
    </div>

    <livewire:lista-tickets />
@endsection
