@extends('layouts.app')

@section('title', 'Zonas y técnicos de '.$supervisor->name)

@section('content')
    <div class="mb-5">
        <a href="{{ route('usuarios.index') }}" class="text-sm text-slate-400 hover:text-slate-600">← Volver a usuarios</a>
        <h1 class="text-2xl font-bold text-slate-900 mt-1">🗺️ Zonas y técnicos</h1>
        <p class="text-sm text-slate-500">
            Configurá las zonas que gestiona y los técnicos que tiene a disposición
            <strong>{{ $supervisor->name }}</strong>.
        </p>
    </div>

    @if (session('exito'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('exito') }}
        </div>
    @endif

    <form method="POST" action="{{ route('usuarios.zonas.store', $supervisor) }}" class="space-y-5">
        @csrf

        {{-- ZONAS --}}
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="font-semibold text-slate-900">📍 Zonas a su cargo</h2>
            <p class="text-xs text-slate-500 mt-1">El supervisor solo verá sitios y tickets de estas zonas.</p>

            <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($zonas as $zona)
                    <label class="flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 text-sm ring-1 ring-slate-100">
                        <input type="checkbox" name="zonas[]" value="{{ $zona->id }}"
                               {{ in_array($zona->id, $zonasAsignadas) ? 'checked' : '' }}
                               class="rounded border-slate-300">
                        <span class="font-medium text-slate-700">{{ $zona->nombre }}</span>
                        <span class="text-xs text-slate-400">({{ $zona->codigo }})</span>
                    </label>
                @empty
                    <p class="text-sm text-slate-400">No hay zonas creadas todavía.</p>
                @endforelse
            </div>
        </div>

        {{-- TÉCNICOS --}}
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="font-semibold text-slate-900">👷 Técnicos a su disposición</h2>
            <p class="text-xs text-slate-500 mt-1">Solo podrá asignar tickets a estos técnicos.</p>

            <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @forelse ($todosTecnicos as $tecnico)
                    <label class="flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 text-sm ring-1 ring-slate-100">
                        <input type="checkbox" name="tecnicos[]" value="{{ $tecnico->id }}"
                               {{ in_array($tecnico->id, $tecnicosAsignados) ? 'checked' : '' }}
                               class="rounded border-slate-300">
                        <span class="font-medium text-slate-700">{{ $tecnico->name }}</span>
                        <span class="text-xs text-slate-400">({{ $tecnico->area ?? 'sin área' }})</span>
                    </label>
                @empty
                    <p class="text-sm text-slate-400">No hay técnicos activos.</p>
                @endforelse
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">
                💾 Guardar
            </button>
        </div>
    </form>
@endsection
