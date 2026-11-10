@extends('layouts.app')

@section('title', 'Sitios')

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">🏢 Sitios</h1>
            <p class="text-sm text-slate-500">
                {{ $sitios->total() }} sitios detectados en el Excel, con tickets y técnicos fijos.
            </p>
        </div>
        <form method="GET" action="{{ route('sitios.index') }}" class="flex gap-2">
            <input type="search" name="busqueda" value="{{ $busqueda }}"
                   placeholder="🔍 Buscar sitio o calle…"
                   class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
            <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                Buscar
            </button>
        </form>
    </div>

    @if (session('exito'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('exito') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Sitio</th>
                        <th class="px-4 py-3">Provincia</th>
                        <th class="px-4 py-3">Tickets</th>
                        <th class="px-4 py-3">Técnicos fijos</th>
                        <th class="px-4 py-3">Contrato SLA</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($sitios as $sitio)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900">{{ $sitio->nombre }}</div>
                                <div class="text-xs text-slate-400">{{ $sitio->calle }}</div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $sitio->provincia_nombre }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-blue-200">
                                    {{ $sitio->tickets_count }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                                {{ $sitio->tecnicos_count ?: '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-500">
                                {{ $sitio->contratoSla?->nombre ?? 'default' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <a href="{{ route('sitios.show', $sitio) }}"
                                   class="rounded-md bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-700">
                                    Abrir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-400">
                                No hay sitios. 📭
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-100 px-4 py-3">
            {{ $sitios->links() }}
        </div>
    </div>
@endsection
