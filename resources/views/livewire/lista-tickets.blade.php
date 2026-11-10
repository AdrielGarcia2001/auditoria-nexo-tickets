<div class="space-y-4">

    {{-- totales --}}
    <div class="flex flex-wrap gap-2 text-sm">
        <span class="rounded-full bg-blue-50 px-3 py-1 font-medium text-blue-700 ring-1 ring-blue-200">
            {{ $totales['nuevos'] }} nuevos
        </span>
        <span class="rounded-full bg-amber-50 px-3 py-1 font-medium text-amber-700 ring-1 ring-amber-200">
            {{ $totales['resueltos'] }} resueltos
        </span>
        <span class="rounded-full bg-emerald-50 px-3 py-1 font-medium text-emerald-700 ring-1 ring-emerald-200">
            {{ $totales['cerrados'] }} cerrados
        </span>
    </div>

    {{-- filtros --}}
    <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div class="sm:col-span-2">
                <input type="search" wire:model.live.debounce.400ms="busqueda"
                       placeholder="🔍 Buscar N° de orden, asunto, sitio, calle…"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>

            <select wire:model.live="filtroEstado" wire:change="actualizar"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Todos los estados</option>
                <option value="nuevo">Nuevo</option>
                <option value="resuelto">Resuelto</option>
                <option value="cerrado">Cerrado</option>
            </select>

            <select wire:model.live="filtroPrioridad" wire:change="actualizar"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Toda prioridad</option>
                @foreach ($prioridades as $prioridad)
                    <option value="{{ $prioridad }}">{{ $prioridad }}</option>
                @endforeach
            </select>

            <select wire:model.live="filtroArea" wire:change="actualizar"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Toda área</option>
                <option value="mantenimiento">🔧 Mantenimiento</option>
                <option value="limpieza">🧹 Limpieza</option>
            </select>

            <select wire:model.live="filtroSla" wire:change="actualizar"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Todo SLA</option>
                <option value="por_vencer">⏳ Por vencer (24h)</option>
                <option value="vencido">⚠️ Vencido</option>
            </select>

            <select wire:model.live="orden" wire:change="actualizar"
                    class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="fecha_entrada">Orden: más recientes</option>
                <option value="sla_vencimiento">Orden: SLA primero</option>
                <option value="prioridad">Orden: prioridad</option>
            </select>
        </div>

        <div class="mt-3 flex justify-end">
            <a href="{{ route('tickets.exportar', ['busqueda' => $busqueda, 'estado' => $filtroEstado, 'prioridad' => $filtroPrioridad, 'area' => $filtroArea]) }}"
               class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-300 hover:bg-emerald-50">
                📤 Exportar a Excel (filtros actuales)
            </a>
        </div>
    </div>

    {{-- tabla --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">N° Orden</th>
                        <th class="px-4 py-3">Sitio / Calle</th>
                        <th class="px-4 py-3">Asunto</th>
                        <th class="px-4 py-3">Prioridad</th>
                        <th class="px-4 py-3">SLA</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Técnicos</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($tickets as $ticket)
                        <tr class="hover:bg-slate-50">
                            <td class="whitespace-nowrap px-4 py-3 font-mono text-xs font-semibold text-slate-900">
                                {{ $ticket->numero_orden }}
                                @if ($ticket->reabierto)
                                    <span class="ml-1 rounded bg-purple-100 px-1 text-[10px] text-purple-700">reabierto</span>
                                @endif
                                <div class="text-[10px] font-normal text-slate-400">
                                    {{ $ticket->tipo === 'preventivo' ? '🔵 preventivo' : '🟠 correctivo' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                <div class="font-medium">{{ $ticket->ubicacion ?? $ticket->sitio?->nombre ?? '—' }}</div>
                                <div class="text-xs text-slate-400">{{ $ticket->calle }}</div>
                            </td>
                            <td class="max-w-[18rem] px-4 py-3">
                                <div class="truncate text-slate-700" title="{{ $ticket->texto_breve }}">
                                    {{ $ticket->texto_breve }}
                                </div>
                                @if ($ticket->origen_hoja)
                                    <div class="text-[10px] text-slate-400">hoja: {{ $ticket->origen_hoja }}</div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @include('partials.badge-prioridad', ['prioridad' => $ticket->prioridad])
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs">
                                @if ($ticket->sla_vencimiento)
                                    @if ($ticket->slaVencido())
                                        <span class="font-semibold text-red-600" title="Vencido el {{ $ticket->sla_vencimiento->format('d/m/Y H:i') }}">
                                            ⚠ vencido
                                        </span>
                                    @elseif ($ticket->slaPorVencer())
                                        <span class="font-semibold text-amber-600">
                                            ⏳ {{ $ticket->sla_vencimiento->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="text-slate-500">
                                            {{ $ticket->sla_vencimiento->format('d/m/Y') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-slate-300">sin SLA</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @include('partials.badge-estado', ['estado' => $ticket->estado_workflow])
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-600">
                                @if ($ticket->tecnicos->isEmpty())
                                    <span class="text-slate-300">sin asignar</span>
                                @else
                                    {{ $ticket->tecnicos->pluck('name')->join(', ') }}
                                @endif
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
                            <td colspan="8" class="px-4 py-10 text-center text-slate-400">
                                No hay tickets con esos filtros. 📭
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-100 px-4 py-3">
            {{ $tickets->links() }}
        </div>
    </div>
</div>
