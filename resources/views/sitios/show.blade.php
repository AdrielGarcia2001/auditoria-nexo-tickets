@extends('layouts.app')

@section('title', 'Sitio '.$sitio->nombre)

@section('content')
    <div class="mb-5">
        <a href="{{ route('sitios.index') }}" class="text-sm text-slate-400 hover:text-slate-600">← Volver a sitios</a>
        <h1 class="text-2xl font-bold text-slate-900 mt-1">🏢 {{ $sitio->nombre }}</h1>
        <p class="text-sm text-slate-500">{{ $sitio->calle }} · {{ $sitio->provincia_nombre }}</p>
    </div>

    @if (session('exito'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('exito') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        {{-- tecnicos fijos --}}
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="font-semibold text-slate-900">👷 Técnicos fijos del sitio</h2>
            <form method="POST" action="{{ route('sitios.tecnicos', $sitio) }}" class="mt-3 space-y-2">
                @csrf
                @foreach ($tecnicosDisponibles as $tecnico)
                    <label class="flex items-center gap-2 rounded-md bg-slate-50 px-3 py-2 text-sm">
                        <input type="checkbox" name="tecnicos[]" value="{{ $tecnico->id }}"
                               {{ $sitio->tecnicos->contains($tecnico) ? 'checked' : '' }}
                               class="rounded border-slate-300">
                        {{ $tecnico->name }} <span class="text-xs text-slate-400">({{ $tecnico->area }})</span>
                    </label>
                @endforeach
                <button class="mt-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Guardar técnicos</button>
            </form>
        </div>

        {{-- contrato SLA --}}
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="font-semibold text-slate-900">⏱️ Contrato SLA del sitio</h2>
            <p class="text-xs text-slate-500 mt-1">
                Marcá las prioridades que tienen SLA en este sitio. Las marcadas usan sus horas (24h, 48h, 72h…);
                las desmarcadas <strong>no tienen vencimiento</strong>. Si no hay contrato activo, se usa el default del sistema.
            </p>

            <form method="POST" action="{{ route('sitios.contrato', $sitio) }}" class="mt-3 space-y-3">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Nombre del contrato</label>
                    <input type="text" name="nombre" value="{{ $sitio->contratoSla?->nombre ?? 'SLA '.$sitio->nombre }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Descripción</label>
                    <textarea name="descripcion" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ $sitio->contratoSla?->descripcion }}</textarea>
                </div>

                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    @foreach (array_keys(\App\Models\Ticket::PRIORIDAD_HORAS) as $p)
                        @php
                            $marcada = $sitio->contratoSla && array_key_exists($p, (array) $sitio->contratoSla->horas_por_prioridad);
                            $horasDefault = \App\Models\Ticket::PRIORIDAD_HORAS[$p];
                        @endphp
                        <label class="flex items-center gap-3 rounded-lg border px-3 py-2.5 cursor-pointer transition
                                    {{ $marcada ? 'border-slate-900 bg-slate-50' : 'border-slate-200 bg-white hover:bg-slate-50' }}">
                            <input type="checkbox" name="horas[{{ $p }}]" value="{{ $horasDefault }}" {{ $marcada ? 'checked' : '' }}
                                   class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                            <span class="flex-1 text-sm font-medium text-slate-700">{{ $p }}</span>
                            <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $marcada ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }}">
                                {{ $marcada ? $horasDefault.'h' : 'sin SLA' }}
                            </span>
                        </label>
                    @endforeach
                </div>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="activo" value="1" {{ ($sitio->contratoSla?->activo ?? true) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                    Contrato activo
                </label>
                <div>
                    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Guardar contrato</button>
                </div>
            </form>
        </div>
    </div>

    {{-- tickets del sitio --}}
    <div class="mt-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="font-semibold text-slate-900">🎫 Tickets del sitio</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                    <tr><th class="px-4 py-3">Orden</th><th class="px-4 py-3">Asunto</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($sitio->tickets as $ticket)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-slate-700">{{ $ticket->numero_orden }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $ticket->texto_breve }}</td>
                            <td class="px-4 py-3">@include('partials.badge-estado', ['estado' => $ticket->estado_workflow])</td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('tickets.show', $ticket) }}" class="text-xs font-semibold text-blue-600 hover:underline">Ver</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-slate-400">Sin tickets.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
