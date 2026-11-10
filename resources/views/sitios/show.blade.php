@extends('layouts.app')

@section('title', $sitio->nombre)

@section('content')
    <div class="mb-4">
        <a href="{{ route('sitios.index') }}" class="text-sm text-slate-400 hover:text-slate-600">← Volver a sitios</a>
        <h1 class="mt-1 text-2xl font-bold text-slate-900">🏢 {{ $sitio->nombre }}</h1>
        <p class="text-sm text-slate-500">
            {{ $sitio->calle }} · {{ $sitio->provincia_nombre }}
            @if ($sitio->lat && $sitio->lng)
                · 📍 {{ $sitio->lat }}, {{ $sitio->lng }}
            @endif
        </p>
    </div>

    @if (session('exito'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('exito') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        {{-- técnicos fijos --}}
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="font-semibold text-slate-900">👷 Técnicos fijos del sitio</h2>
            <p class="text-xs text-slate-500">Asigná los técnicos que tienen este sitio como fijo.</p>

            <form method="POST" action="{{ route('sitios.tecnicos', $sitio) }}" class="mt-3 space-y-2">
                @csrf
                @foreach ($tecnicosDisponibles as $tec)
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="tecnicos[]" value="{{ $tec->id }}"
                               {{ $sitio->tecnicos->contains($tec->id) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                        <span class="text-slate-700">{{ $tec->name }}</span>
                        <span class="text-xs text-slate-400">({{ $tec->area ?? $tec->role }})</span>
                    </label>
                @endforeach
                <div class="pt-2">
                    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                        Guardar técnicos
                    </button>
                </div>
            </form>
        </div>

        {{-- contrato SLA --}}
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="font-semibold text-slate-900">⏱️ Contrato SLA del sitio</h2>
            <p class="text-xs text-slate-500">
                Override de horas por prioridad para este sitio (vacío = default del sistema).
            </p>

            <form method="POST" action="{{ route('sitios.contrato', $sitio) }}" class="mt-3 space-y-3">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Nombre del contrato</label>
                    <input type="text" name="nombre" value="{{ $sitio->contratoSla?->nombre ?? 'SLA '.$sitio->nombre }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Descripción</label>
                    <textarea name="descripcion" rows="2"
                              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">{{ $sitio->contratoSla?->descripcion }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach (array_keys(\App\Models\Ticket::PRIORIDAD_HORAS) as $p)
                        <div>
                            <label class="mb-1 block text-xs text-slate-500">{{ $p }}</label>
                            <input type="number" name="horas[{{ $p }}]"
                                   value="{{ $sitio->contratoSla?->horas_por_prioridad[$p] ?? '' }}"
                                   placeholder="default"
                                   class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                        </div>
                    @endforeach
                </div>
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="activo" value="1" {{ ($sitio->contratoSla?->activo ?? true) ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                    Contrato activo
                </label>
                <div>
                    <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                        Guardar contrato
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- tickets del sitio --}}
    <div class="mt-6 overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="font-semibold text-slate-900">Tickets del sitio ({{ $sitio->tickets->count() }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">N° Orden</th>
                        <th class="px-4 py-3">Asunto</th>
                        <th class="px-4 py-3">Prioridad</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($sitio->tickets as $ticket)
                        <tr class="hover:bg-slate-50">
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-xs font-semibold text-slate-900">
                                {{ $ticket->numero_orden }}
                            </td>
                            <td class="max-w-[20rem] truncate px-4 py-3 text-slate-700" title="{{ $ticket->texto_breve }}">
                                {{ $ticket->texto_breve }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @include('partials.badge-prioridad', ['prioridad' => $ticket->prioridad])
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @include('partials.badge-estado', ['estado' => $ticket->estado_workflow])
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <a href="{{ route('tickets.show', $ticket) }}"
                                   class="rounded-md bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-700">
                                    Abrir
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-slate-400">
                                Sin tickets para este sitio.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
