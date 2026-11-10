@extends('layouts.app')

@section('title', 'Alertas SLA')

@section('content')
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">⏰ Alertas SLA</h1>
            <p class="text-sm text-slate-500">
                Tickets por vencer (24h) y vencidos, generados por el scheduler.
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('alertas.index', ['tipo' => '']) }}"
               class="rounded-lg px-3 py-2 text-sm font-semibold ring-1 {{ $tipo === '' ? 'bg-slate-900 text-white ring-slate-900' : 'bg-white text-slate-700 ring-slate-300 hover:bg-slate-50' }}">
                Todas
            </a>
            <a href="{{ route('alertas.index', ['tipo' => 'por_vencer']) }}"
               class="rounded-lg px-3 py-2 text-sm font-semibold ring-1 {{ $tipo === 'por_vencer' ? 'bg-amber-600 text-white ring-amber-600' : 'bg-white text-amber-700 ring-amber-300 hover:bg-amber-50' }}">
                ⏳ Por vencer ({{ $totales['por_vencer'] }})
            </a>
            <a href="{{ route('alertas.index', ['tipo' => 'vencido']) }}"
               class="rounded-lg px-3 py-2 text-sm font-semibold ring-1 {{ $tipo === 'vencido' ? 'bg-red-600 text-white ring-red-600' : 'bg-white text-red-700 ring-red-300 hover:bg-red-50' }}">
                ⚠️ Vencidos ({{ $totales['vencido'] }})
            </a>
            <form method="POST" action="{{ route('alertas.leer-todas') }}">
                @csrf
                <button class="rounded-lg bg-white px-3 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50">
                    Marcar todas leídas
                </button>
            </form>
        </div>
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
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Ticket</th>
                        <th class="px-4 py-3">Mensaje</th>
                        <th class="px-4 py-3">Creada</th>
                        <th class="px-4 py-3">Leída</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($alertas as $alerta)
                        <tr class="hover:bg-slate-50 {{ $alerta->leida_at ? 'opacity-60' : '' }}">
                            <td class="whitespace-nowrap px-4 py-3">
                                @if ($alerta->tipo === \App\Models\AlertaSla::TIPO_VENCIDO)
                                    <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700 ring-1 ring-red-200">vencido</span>
                                @else
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">por vencer</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-xs font-semibold text-slate-900">
                                @if ($alerta->ticket)
                                    <a href="{{ route('tickets.show', $alerta->ticket) }}" class="hover:underline">
                                        #{{ $alerta->ticket->numero_orden }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $alerta->mensaje }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-500">
                                {{ $alerta->created_at?->format('d/m/Y H:i') }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-500">
                                {{ $alerta->leida_at?->format('d/m/Y H:i') ?? '—' }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                @if (! $alerta->leida_at)
                                    <form method="POST" action="{{ route('alertas.leer', $alerta) }}">
                                        @csrf
                                        <button class="rounded-md bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50">
                                            Marcar leída
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-400">
                                No hay alertas. 🎉
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-100 px-4 py-3">
            {{ $alertas->links() }}
        </div>
    </div>
@endsection
