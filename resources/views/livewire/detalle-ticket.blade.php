<div class="space-y-4">

    {{-- ============================================================
         CABECERA
    ============================================================ --}}
    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <a href="{{ route('tickets.index') }}" class="text-sm text-slate-400 hover:text-slate-600">← Volver al listado</a>
                <h1 class="mt-1 font-mono text-xl font-bold text-slate-900">
                    #{{ $ticket->numero_orden }}
                    @if ($ticket->reabierto)
                        <span class="ml-1 rounded bg-purple-100 px-2 py-0.5 text-xs font-sans text-purple-700">REABIERTO</span>
                    @endif
                </h1>
                <p class="text-sm text-slate-600">{{ $ticket->texto_breve }}</p>
                <p class="text-xs text-slate-400">
                    {{ $ticket->ubicacion }} · {{ $ticket->calle }} · {{ $ticket->provincia_nombre }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 text-xs">
                @include('partials.badge-estado', ['estado' => $ticket->estado_workflow])
                <span class="rounded-full px-2 py-0.5 font-semibold ring-1 {{ $ticket->tipo === 'preventivo' ? 'bg-blue-100 text-blue-700 ring-blue-200' : 'bg-orange-100 text-orange-700 ring-orange-200' }}">
                    {{ $ticket->tipo }}
                </span>
                @include('partials.badge-prioridad', ['prioridad' => $ticket->prioridad])

                @if ($ticket->sla_vencimiento)
                    <span class="rounded-full px-2 py-0.5 font-semibold ring-1
                        {{ $ticket->slaVencido() ? 'bg-red-100 text-red-700 ring-red-200'
                           : ($ticket->slaPorVencer() ? 'bg-amber-100 text-amber-700 ring-amber-200'
                           : 'bg-slate-100 text-slate-600 ring-slate-200') }}">
                        SLA {{ $ticket->slaVencido() ? 'vencido' : $ticket->sla_vencimiento->format('d/m/Y H:i') }}
                    </span>
                @endif
            </div>
        </div>

        {{-- mensajes --}}
        @if ($exito)
            <div class="mt-3 rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-700 ring-1 ring-emerald-200">{{ $exito }}</div>
        @endif
        @if ($errorAccion)
            <div class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 ring-1 ring-red-200">{{ $errorAccion }}</div>
        @endif

        {{-- acciones segun rol y estado --}}
        <div class="mt-4 flex flex-wrap gap-2">
            @if ($puedeResolver)
                <button wire:click="$toggle('mostrarResolver')"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    ✅ Resolver ticket
                </button>
            @endif

            @if ($puedeCerrar)
                <button wire:click="$toggle('mostrarCerrar')"
                        class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                    🔒 Cerrar ticket
                </button>
                <button wire:click="$toggle('mostrarReabrir')"
                        class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white hover:bg-purple-700">
                    🔄 Reabrir
                </button>
            @endif

            @if ($puedeAsignar)
                <button wire:click="$toggle('mostrarAsignar')"
                        class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50">
                    👷 {{ $ticket->tecnicos->isEmpty() ? 'Asignar técnicos' : 'Reasignar' }}
                </button>
            @endif

            @if ($ticket->drive_folder_url)
                <a href="{{ $ticket->drive_folder_url }}" target="_blank" rel="noopener"
                   class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-blue-700 ring-1 ring-blue-300 hover:bg-blue-50">
                    📂 Carpeta en Drive
                </a>
            @endif

            @if ($usuario->isAdmin())
                <button wire:click="abrirEditar"
                        class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50">
                    ✏️ Editar datos (Excel)
                </button>
            @endif
        </div>

        {{-- form RESOLVER --}}
        @if ($mostrarResolver)
            <div class="mt-3 rounded-lg bg-emerald-50 p-4 ring-1 ring-emerald-200">
                <label class="block text-sm font-medium text-emerald-900">
                    Comentario de resolución (opcional, se guarda en Drive)
                </label>
                <textarea wire:model="comentarioResolucion" rows="2"
                          class="mt-1 w-full rounded-lg border border-emerald-300 px-3 py-2 text-sm"
                          placeholder="Ej: Tarea realizada, equipo funcionando…"></textarea>
                <div class="mt-2 flex gap-2">
                    <button wire:click="resolver" wire:loading.attr="disabled"
                            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-50">
                        Confirmar resolución
                    </button>
                    <button wire:click="$toggle('mostrarResolver')"
                            class="rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-emerald-100">Cancelar</button>
                </div>
            </div>
        @endif

        {{-- form CERRAR --}}
        @if ($mostrarCerrar)
            <div class="mt-3 rounded-lg bg-slate-50 p-4 ring-1 ring-slate-200">
                <label class="block text-sm font-medium text-slate-700">Comentario de cierre (opcional)</label>
                <textarea wire:model="comentarioCierre" rows="2"
                          class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                <div class="mt-2 flex gap-2">
                    <button wire:click="cerrar" wire:loading.attr="disabled"
                            class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white disabled:opacity-50">
                        Confirmar cierre
                    </button>
                    <button wire:click="$toggle('mostrarCerrar')"
                            class="rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-slate-100">Cancelar</button>
                </div>
            </div>
        @endif

        {{-- form REABRIR --}}
        @if ($mostrarReabrir)
            <div class="mt-3 rounded-lg bg-purple-50 p-4 ring-1 ring-purple-200">
                <label class="flex items-center gap-2 text-sm font-medium text-purple-900">
                    <input type="checkbox" wire:model="convertirACorrectivo" class="rounded border-purple-300">
                    Cambiar de preventivo a correctivo al reabrir
                </label>
                <label class="mt-2 block text-sm font-medium text-purple-900">Motivo de reapertura (opcional)</label>
                <textarea wire:model="motivoReapertura" rows="2"
                          class="mt-1 w-full rounded-lg border border-purple-300 px-3 py-2 text-sm"
                          placeholder="Por qué se reabre el ticket…"></textarea>
                <div class="mt-2 flex gap-2">
                    <button wire:click="reabrir" wire:loading.attr="disabled"
                            class="rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white hover:bg-purple-700 disabled:opacity-50">
                        Confirmar reapertura
                    </button>
                    <button wire:click="$toggle('mostrarReabrir')"
                            class="rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-purple-100">Cancelar</button>
                </div>
            </div>
        @endif

        {{-- form ASIGNAR --}}
        @if ($mostrarAsignar)
            <div class="mt-3 rounded-lg bg-blue-50 p-4 ring-1 ring-blue-200">
                <p class="text-sm font-medium text-blue-900">
                    Elegí los técnicos (máx. {{ $ticket->limiteTecnicos() }} para {{ $ticket->area() }}):
                </p>
                <div class="mt-2 grid grid-cols-1 gap-1 sm:grid-cols-2">
                    @foreach ($tecnicosDisponibles as $tecnico)
                        <label class="flex items-center gap-2 rounded-md bg-white px-2 py-1.5 text-sm ring-1 ring-blue-100">
                            <input type="checkbox" wire:model.live="tecnicosSeleccionados"
                                   value="{{ $tecnico->id }}" class="rounded border-blue-300">
                            {{ $tecnico->name }}
                            <span class="text-xs text-slate-400">({{ $tecnico->area ?? 'sin área' }})</span>
                        </label>
                    @endforeach
                </div>
                <div class="mt-3 flex gap-2">
                    <button wire:click="guardarAsignacion" wire:loading.attr="disabled"
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
                        Guardar asignación
                    </button>
                    <button wire:click="$toggle('mostrarAsignar')"
                            class="rounded-lg px-4 py-2 text-sm text-slate-600 hover:bg-blue-100">Cancelar</button>
                </div>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

        {{-- ============================================================
             COLUMNAS IZQUIERDA: DATOS DEL EXCEL
        ============================================================ --}}
        <div class="space-y-4 lg:col-span-2">

            {{-- campos exactos del Excel --}}
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <h2 class="font-semibold text-slate-900">📄 Datos del Excel</h2>
                <dl class="mt-3 grid grid-cols-1 gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
                    @php
                        $campos = [
                            'MDA' => $ticket->mda,
                            'N° ORDEN' => $ticket->numero_orden,
                            'CLASE ORDEN' => $ticket->clase_orden,
                            'FECHA ENTRADA' => $ticket->fecha_entrada?->format('d/m/Y'),
                            'FECHA MODIF. MAESTRO' => $ticket->fecha_modificacion_maestro?->format('d/m/Y'),
                            'GRUPO PLANIFICADOR' => $ticket->grupo_planificador,
                            'TEXTO BREVE' => $ticket->texto_breve,
                            'CALLE' => $ticket->calle,
                            'UBICACION' => $ticket->ubicacion,
                            'PROVINCIA' => $ticket->provincia_codigo.' · '.$ticket->provincia_nombre,
                            'INICIO EXTREMA' => $ticket->inicio_extrema?->format('d/m/Y'),
                            'FIN EXTREMA' => $ticket->fin_extrema?->format('d/m/Y'),
                            'ESTATUS' => $ticket->estatus,
                            'PRIORIDAD' => $ticket->prioridad,
                            'ESTADO (Excel)' => $ticket->estado,
                            'LO RESOLVIÓ' => $ticket->lo_resolvio,
                            'FECHA DE RESOLUCIÓN' => $ticket->fecha_resolucion?->format('d/m/Y'),
                            'AÑO' => $ticket->anio,
                            'ÁREA DE EMPRESA' => $ticket->area_empresa,
                            'DOC. ADJ.' => $ticket->doc_adjunto,
                            'HOJA / FILA' => ($ticket->origen_hoja ? $ticket->origen_hoja.' · fila '.$ticket->fila_origen : null),
                        ];
                    @endphp
                    @foreach ($campos as $etiqueta => $valor)
                        <div class="flex gap-2 border-b border-slate-50 py-1">
                            <dt class="w-44 shrink-0 text-xs font-semibold uppercase tracking-wide text-slate-400">{{ $etiqueta }}</dt>
                            <dd class="min-w-0 flex-1 break-words text-slate-700">
                                {{ $valor ?? '—' }}
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>

            {{-- descripcion --}}
            @if ($ticket->descripcion)
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <h2 class="font-semibold text-slate-900">📝 Descripción</h2>
                    <p class="mt-2 max-h-64 overflow-auto whitespace-pre-line text-sm text-slate-600">{{ $ticket->descripcion }}</p>
                </div>
            @endif

            {{-- comentarios del Excel --}}
            @if ($ticket->comentarios || $ticket->comentarios_extra || $ticket->fotos_texto)
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <h2 class="font-semibold text-slate-900">💬 Comentarios del Excel</h2>
                    @if ($ticket->comentarios)
                        <p class="mt-2 whitespace-pre-line text-sm text-slate-600">{{ $ticket->comentarios }}</p>
                    @endif
                    @if ($ticket->fotos_texto)
                        <p class="mt-2 text-sm text-slate-500"><strong>Fotos:</strong> {{ $ticket->fotos_texto }}</p>
                    @endif
                    @if ($ticket->comentarios_extra)
                        <p class="mt-2 text-sm text-slate-500"><strong>Comentarios:</strong> {{ $ticket->comentarios_extra }}</p>
                    @endif
                </div>
            @endif

            {{-- documentacion --}}
            @if ($ticket->documentacion)
                <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <h2 class="font-semibold text-slate-900">🔗 Documentación</h2>
                    <a href="{{ $ticket->documentacion }}" target="_blank" rel="noopener"
                       class="mt-2 block break-all text-sm text-blue-600 hover:underline">{{ $ticket->documentacion }}</a>
                </div>
            @endif
        </div>

        {{-- ============================================================
             COLUMNA DERECHA: TECNICOS, FOTOS, COMENTARIOS, HISTORIAL
        ============================================================ --}}
        <div class="space-y-4">

            {{-- tecnicos asignados --}}
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <h2 class="font-semibold text-slate-900">👷 Técnicos</h2>
                @if ($ticket->tecnicos->isEmpty())
                    <p class="mt-2 text-sm text-slate-400">Sin asignar.</p>
                @else
                    <ul class="mt-2 space-y-1 text-sm">
                        @foreach ($ticket->tecnicos as $tecnico)
                            <li class="flex items-center justify-between rounded-md bg-slate-50 px-2 py-1.5">
                                <span>{{ $tecnico->name }}</span>
                                <span class="text-xs text-slate-400">{{ $tecnico->area }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- subir fotos (antes / despues) --}}
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <h2 class="font-semibold text-slate-900">📷 Fotos</h2>

                @if ($ticket->fotos->isNotEmpty())
                    <div class="mt-2 grid grid-cols-3 gap-2">
                        @foreach ($ticket->fotos as $foto)
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($foto->ruta) }}" target="_blank"
                               class="group relative block aspect-square overflow-hidden rounded-lg ring-1 ring-slate-200">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($foto->ruta) }}"
                                     alt="Foto {{ $foto->tipo }}" class="h-full w-full object-cover">
                                <span class="absolute bottom-0 left-0 right-0 bg-black/60 px-1 py-0.5 text-center text-[10px] text-white">
                                    {{ $foto->tipo }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif

                <div class="mt-3 space-y-2" x-data>
                    <select wire:model="tipoFoto"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="antes">Foto ANTES</option>
                        <option value="despues">Foto DESPUÉS</option>
                    </select>

                    <input type="file" wire:model="foto" accept="image/*"
                           class="block w-full text-xs text-slate-500 file:mr-2 file:rounded-lg file:border-0 file:bg-slate-900 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white">
                    @error('foto')
                        <p class="text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    {{-- toma la foto con GPS y la sube --}}
                    <button type="button"
                            wire:loading.attr="disabled"
                            @click="
                                if (! navigator.geolocation) { $wire.subirFoto(null, null); return; }
                                navigator.geolocation.getCurrentPosition(
                                    p => $wire.subirFoto(p.coords.latitude, p.coords.longitude),
                                    () => $wire.subirFoto(null, null),
                                    { enableHighAccuracy: true, timeout: 8000 }
                                );
                            "
                            class="w-full rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-700 disabled:opacity-50">
                        <span wire:loading.remove wire:target="subirFoto">Subir foto (con GPS)</span>
                        <span wire:loading wire:target="subirFoto">⏳ Subiendo…</span>
                    </button>
                    <p class="text-[11px] text-slate-400">Se guarda la ubicación GPS al momento de tomarla.</p>
                </div>
            </div>

            {{-- comentarios del sistema --}}
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <h2 class="font-semibold text-slate-900">💬 Comentarios</h2>

                <div class="mt-2 flex gap-2">
                    <input type="text" wire:model="nuevoComentario" wire:keydown.enter="agregarComentario"
                           placeholder="Escribí un comentario…"
                           class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <button wire:click="agregarComentario"
                            class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                        ➤
                    </button>
                </div>

                <ul class="mt-3 space-y-2">
                    @forelse ($ticket->comentariosSistema as $comentario)
                        <li class="rounded-lg bg-slate-50 p-2.5 text-sm ring-1 ring-slate-100">
                            <div class="flex items-center justify-between text-xs text-slate-400">
                                <span class="font-medium text-slate-600">{{ $comentario->user?->name }}</span>
                                <span>{{ $comentario->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <p class="mt-1 whitespace-pre-line text-slate-700">{{ $comentario->texto }}</p>
                            @if ($comentario->tipo !== 'general')
                                <span class="mt-1 inline-block rounded bg-slate-200 px-1.5 text-[10px] uppercase text-slate-500">{{ $comentario->tipo }}</span>
                            @endif
                        </li>
                    @empty
                        <li class="text-sm text-slate-400">Sin comentarios.</li>
                    @endforelse
                </ul>
            </div>

            {{-- historial --}}
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <h2 class="font-semibold text-slate-900">🕘 Historial</h2>
                <ul class="mt-2 space-y-2 border-l border-slate-200 pl-3 text-sm">
                    @forelse ($ticket->historial as $evento)
                        <li class="relative">
                            <span class="absolute -left-[1.4rem] top-1.5 h-2 w-2 rounded-full bg-slate-400"></span>
                            <div class="text-slate-700">
                                <strong class="capitalize">{{ $evento->accion }}</strong>
                                @if ($evento->desde || $evento->hasta)
                                    <span class="text-slate-400">
                                        ({{ $evento->desde }} → {{ $evento->hasta }})
                                    </span>
                                @endif
                            </div>
                            <div class="text-xs text-slate-400">
                                {{ $evento->user?->name }} · {{ $evento->created_at?->format('d/m/Y H:i') }}
                            </div>
                        </li>
                    @empty
                        <li class="text-slate-400">Sin eventos.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- ============================================================
         MODAL: editar datos del Excel (solo admin)
    ============================================================ --}}
    @if ($mostrarEditar)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4">
            <div class="w-full max-w-2xl rounded-xl bg-white p-6 shadow-xl">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">✏️ Editar datos del ticket (Excel)</h2>
                    <button wire:click="$set('mostrarEditar', false)" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Texto breve</label>
                        <input type="text" wire:model="edit_texto_breve"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Descripción</label>
                        <textarea wire:model="edit_descripcion" rows="3"
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"></textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Ubicación (sitio)</label>
                        <input type="text" wire:model="edit_ubicacion"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Calle</label>
                        <input type="text" wire:model="edit_calle"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Prioridad</label>
                        <select wire:model="edit_prioridad"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                            <option value="">— sin prioridad —</option>
                            @foreach (array_keys(\App\Models\Ticket::PRIORIDAD_HORAS) as $p)
                                <option value="{{ $p }}">{{ $p }}</option>
                            @endforeach
                            <option value="Programado Mensual">Programado Mensual</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Estado (Excel)</label>
                        <input type="text" wire:model="edit_estado"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Estatus</label>
                        <input type="text" wire:model="edit_estatus"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Lo resolvió</label>
                        <input type="text" wire:model="edit_lo_resolvio"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Comentarios (resolución)</label>
                        <textarea wire:model="edit_comentarios" rows="2"
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Comentarios extra</label>
                        <textarea wire:model="edit_comentarios_extra" rows="2"
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200"></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-sm font-medium text-slate-700">Doc. adjunto</label>
                        <input type="text" wire:model="edit_doc_adjunto"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    </div>
                </div>

                <div class="mt-5 flex justify-end gap-2">
                    <button wire:click="$set('mostrarEditar', false)"
                            class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50">
                        Cancelar
                    </button>
                    <button wire:click="guardarEdicion"
                            class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                        💾 Guardar cambios
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
