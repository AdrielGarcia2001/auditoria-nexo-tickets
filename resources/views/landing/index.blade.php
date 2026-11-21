@extends('layouts.landing')

@section('title', 'SIGO-NEXO · Sistema Integrado de Gestión Operativa')

@section('content')
{{-- ============================================================
     NAVBAR
============================================================ --}}
<header class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/80 backdrop-blur dark:border-white/5 dark:bg-night-800/80">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
        <a href="#inicio" class="flex items-center gap-3">
            <img src="/images/sigo-nexo-mark.svg" alt="SIGO-NEXO" class="h-9 w-9">
            <div class="leading-tight">
                <div class="text-base font-extrabold tracking-tight text-slate-900 dark:text-white">SIGO<span class="text-blue-700">-NEXO</span></div>
                <div class="hidden text-[10px] font-medium uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400 sm:block">Gestión operativa</div>
            </div>
        </a>

        <nav class="hidden items-center gap-7 text-sm font-medium text-slate-600 dark:text-slate-300 md:flex">
            <a href="#inicio" class="hover:text-cyan-600 dark:hover:text-cyan-400">Solución</a>
            <a href="#pilares" class="hover:text-cyan-600 dark:hover:text-cyan-400">Pilares</a>
            <a href="#flujo" class="hover:text-cyan-600 dark:hover:text-cyan-400">Cómo funciona</a>
        </nav>

        <div class="flex items-center gap-2">
            <button type="button" onclick="alternarTema()" aria-label="Cambiar tema"
                    class="flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:bg-slate-100 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/5">
                <span id="icono-tema">🌙</span>
            </button>
            @auth
                <a href="{{ route('dashboard') }}" class="rounded-lg bg-cyan-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-500">Ir al Dashboard →</a>
            @else
                <a href="{{ route('login') }}" class="hidden rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:text-slate-200 dark:hover:bg-white/5 sm:block">Iniciar sesión</a>
            @endauth
        </div>
    </div>
</header>

{{-- ============================================================
     HERO
============================================================ --}}
<section id="inicio" class="relative overflow-hidden bg-slate-50 dark:bg-night-900">
    <div class="pointer-events-none absolute inset-0 opacity-[0.35] dark:opacity-[0.18]"
         style="background-image:linear-gradient(rgba(148,163,184,.28) 1px,transparent 1px),linear-gradient(90deg,rgba(148,163,184,.28) 1px,transparent 1px);background-size:44px 44px;"></div>
    <div class="pointer-events-none absolute -left-40 top-10 h-[26rem] w-[26rem] rounded-full bg-cyan-300/30 blur-3xl dark:bg-cyan-500/10"></div>
    <div class="pointer-events-none absolute -right-40 bottom-0 h-[24rem] w-[24rem] rounded-full bg-blue-300/30 blur-3xl dark:bg-blue-600/10"></div>
    <div class="pointer-events-none absolute left-1/2 top-1/3 h-72 w-72 -translate-x-1/2 rounded-full border border-cyan-300/30 dark:border-cyan-500/10"></div>
    <div class="pointer-events-none absolute left-1/2 top-1/3 h-[26rem] w-[26rem] -translate-x-1/2 rounded-full border border-cyan-300/20 dark:border-cyan-500/5"></div>

    <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-4 py-14 sm:px-6 lg:grid-cols-[1.05fr_1.2fr] lg:gap-12 lg:px-8 lg:py-20">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-cyan-300/60 bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700 dark:border-cyan-500/30 dark:bg-cyan-500/10 dark:text-cyan-300">
                <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-cyan-500"></span>
                Auditoría operativa en tiempo real
            </span>

            <img src="/images/sigo-nexo-logo.svg" alt="SIGO-NEXO · Sistema Integrado de Gestión Operativa"
                 class="mt-6 w-full max-w-md rounded-xl bg-white p-2 shadow-sm ring-1 ring-slate-200 dark:bg-white/95 dark:ring-white/10">

            <h1 class="mt-6 text-3xl font-extrabold leading-tight tracking-tight text-slate-900 dark:text-white sm:text-4xl lg:text-[2.75rem]">
                Trazabilidad total del ticket
                <span class="bg-gradient-to-r from-cyan-500 to-blue-600 bg-clip-text text-transparent">a la evidencia.</span>
            </h1>

            <p class="mt-5 max-w-xl text-base leading-relaxed text-slate-600 dark:text-slate-300 sm:text-lg">
                SIGO-NEXO integra la auditoría en tiempo real, el control de tickets con SLA
                y la gestión de cuadrillas técnicas en campo. Cada intervención queda sellada
                con evidencia inmutable.
            </p>

            <div class="mt-8 flex flex-wrap items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-xl bg-cyan-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-600/25 hover:bg-cyan-500">Abrir mi panel →</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-xl bg-cyan-600 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-600/25 hover:bg-cyan-500">Ingresar al sistema →</a>
                @endauth
                <a href="#flujo" class="rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-white/10 dark:bg-white/5 dark:text-slate-200 dark:hover:bg-white/10">Ver el flujo</a>
            </div>

            <dl class="mt-10 grid max-w-lg grid-cols-3 gap-4 border-t border-slate-200 pt-6 dark:border-white/10">
                <div><dt class="font-mono text-2xl font-bold text-slate-900 dark:text-white">100%</dt><dd class="text-xs text-slate-500 dark:text-slate-400">trazabilidad</dd></div>
                <div><dt class="font-mono text-2xl font-bold text-slate-900 dark:text-white">24/7</dt><dd class="text-xs text-slate-500 dark:text-slate-400">monitoreo SLA</dd></div>
                <div><dt class="font-mono text-2xl font-bold text-slate-900 dark:text-white">GPS</dt><dd class="text-xs text-slate-500 dark:text-slate-400">cuadrillas en campo</dd></div>
            </dl>
        </div>

        {{-- SIMULADOR SIGO-NEXO (Alpine.js) --}}
        <div x-data="simuladorSigo()" x-init="iniciar()" class="relative">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-2xl shadow-slate-300/40 dark:border-white/10 dark:bg-night-800 dark:shadow-black/40 sm:p-5">
                <div class="mb-3 grid grid-cols-4 gap-1.5">
                    <template x-for="(etapa, i) in etapas" :key="etapa.codigo">
                        <button type="button" @click="irA(i)"
                                class="rounded-lg border px-1 py-2 text-center transition"
                                :class="actual === i ? 'border-cyan-500 bg-cyan-500/10 text-cyan-700 dark:border-cyan-400 dark:text-cyan-300' : 'border-slate-200 text-slate-500 hover:bg-slate-50 dark:border-white/10 dark:text-slate-400 dark:hover:bg-white/5'">
                            <span class="block font-mono text-[10px] font-bold" x-text="etapa.codigo"></span>
                            <span class="block text-[10px] font-bold sm:text-xs" x-text="etapa.titulo"></span>
                        </button>
                    </template>
                </div>
                <div class="mb-4 h-1.5 overflow-hidden rounded-full bg-slate-200 dark:bg-white/10">
                    <div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-emerald-500 transition-all duration-100" :style="`width:${pctGlobal}%`"></div>
                </div>

                <div class="grid gap-4 lg:grid-cols-2">
                    {{-- PANEL IZQUIERDO: MAPA GPS --}}
                    <div class="relative overflow-hidden rounded-xl border border-slate-700 bg-night-900 ring-1 ring-white/5">
                        <svg viewBox="0 0 400 300" class="h-full w-full" preserveAspectRatio="xMidYMid slice" aria-hidden="true">
                            <defs>
                                <pattern id="radar" width="26" height="26" patternUnits="userSpaceOnUse">
                                    <path d="M26 0H0v26" fill="none" stroke="#1e293b" stroke-width="1"/>
                                </pattern>
                            </defs>
                            <rect width="400" height="300" fill="#070b14"/>
                            <rect width="400" height="300" fill="url(#radar)"/>
                            <g stroke="#1e3a5f" stroke-width="2" fill="none" opacity=".9"><path d="M-10 250 C80 210 120 160 190 150 C260 140 300 90 410 60"/></g>
                            <g stroke="#0f2c4d" stroke-width="6" fill="none" opacity=".5"><path d="M-10 250 C80 210 120 160 190 150 C260 140 300 90 410 60"/></g>
                            <path d="M-10 250 C80 210 120 160 190 150 C260 140 300 90 410 60" fill="none" stroke="#22d3ee" stroke-width="2.5" stroke-dasharray="6 6" opacity=".9"/>
                            <g transform="translate(300 90)">
                                <circle r="20" fill="#22d3ee" opacity=".15"><animate attributeName="r" values="16;24;16" dur="2s" repeatCount="indefinite"/></circle>
                                <circle r="8" fill="#06b6d4"/><circle r="3" fill="#fff"/>
                            </g>
                            <g fill="#0f172a" stroke="#1e293b">
                                <rect x="40" y="60" width="52" height="38" rx="3"/><rect x="100" y="180" width="44" height="52" rx="3"/>
                                <rect x="230" y="190" width="56" height="40" rx="3"/><rect x="330" y="180" width="48" height="60" rx="3"/>
                            </g>
                            <g :style="`transform:translate(${xMarcador}px, ${yMarcador}px)`" style="transition:transform .12s linear">
                                <circle r="14" fill="#22d3ee" opacity=".25"/><circle r="8" fill="#22d3ee"/><circle r="3" fill="#070b14"/>
                                <text x="12" y="4" font-size="9" font-weight="700" fill="#67e8f9" font-family="monospace">CUADRILLA</text>
                            </g>
                        </svg>

                        <span class="absolute left-3 top-3 rounded bg-black/40 px-2 py-1 font-mono text-[10px] text-cyan-300">SECTOR: NODO NORTE-04</span>
                        <span class="absolute right-3 top-3 rounded px-2 py-1 text-[10px] font-bold"
                              :class="actual >= 3 ? 'bg-emerald-500/15 text-emerald-300' : 'bg-cyan-500/15 text-cyan-300'"
                              x-text="actual >= 3 ? 'SLA: CUMPLIDO ✓' : 'SLA: ' + slaRestante"></span>

                        <div class="absolute inset-x-3 bottom-3 rounded-lg border border-white/10 bg-black/50 px-3 py-2 backdrop-blur">
                            <p class="text-[11px] text-slate-200" x-text="estadoMapa"></p>
                            <p class="mt-0.5 font-mono text-[10px] text-cyan-300/80">-26.1849° S, -58.1731° W</p>
                        </div>
                    </div>

                    {{-- PANEL DERECHO: ORDEN DE SERVICIO --}}
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-night-700/60">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="font-mono text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Orden de servicio</p>
                                <h3 class="mt-1 text-sm font-extrabold text-slate-900 dark:text-white">#TK-8492 · Fibra Troncal</h3>
                            </div>
                            <span class="rounded-full px-2.5 py-1 text-[10px] font-bold"
                                  :class="actual >= 3 ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-300' : 'bg-cyan-500/15 text-cyan-700 dark:text-cyan-300'"
                                  x-text="actual >= 3 ? 'AUDITADO · CERRADO' : 'EN CURSO'"></span>
                        </div>

                        <div class="mt-3 space-y-1.5 font-mono text-[11px] text-slate-500 dark:text-slate-400">
                            <p>Cuadrilla: <span class="text-slate-800 dark:text-slate-200" x-text="actual >= 2 ? 'Alfa-07 (En sitio)' : 'Alfa-07 (En ruta)'"></span></p>
                            <p>Geolocalización: <span class="text-slate-800 dark:text-slate-200" x-text="actual >= 3 ? 'Acta #AUD-9941 emitida' : 'Tracking en vivo'"></span></p>
                        </div>

                        <div class="mt-4 flex items-center justify-between">
                            <p class="font-mono text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Protocolo de auditoría</p>
                            <span class="font-mono text-xs font-bold text-cyan-600 dark:text-cyan-300" x-text="Math.round(pctGlobal) + '%'"></span>
                        </div>
                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-slate-200 dark:bg-white/10">
                            <div class="h-full rounded-full bg-gradient-to-r from-cyan-500 to-emerald-500 transition-all duration-100" :style="`width:${pctGlobal}%`"></div>
                        </div>

                        <ul class="mt-3 space-y-1.5">
                            <template x-for="(item, i) in protocolo" :key="item">
                                <li class="flex items-center gap-2 rounded-lg border px-2.5 py-1.5 text-xs transition-colors"
                                    :class="i < itemsValidados ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' : 'border-slate-200 text-slate-500 dark:border-white/10 dark:text-slate-400'">
                                    <span x-text="i < itemsValidados ? '✓' : '○'"></span>
                                    <span class="flex-1" x-text="item"></span>
                                    <span x-show="i < itemsValidados" class="font-mono text-[9px] font-bold">VALIDADO</span>
                                </li>
                            </template>
                        </ul>

                        <div class="mt-4 flex items-center justify-between rounded-lg border border-dashed px-3 py-2"
                             :class="actual >= 3 ? 'border-emerald-500/40 bg-emerald-500/5' : 'border-slate-300 dark:border-white/15'">
                            <span class="text-[10px] font-bold" :class="actual >= 3 ? 'text-emerald-600 dark:text-emerald-300' : 'text-slate-400 dark:text-slate-500'">Auditoría Aprobada · Evidencia Inmutable</span>
                            <span class="font-mono text-[10px] font-bold" :class="actual >= 3 ? 'text-emerald-500' : 'text-slate-300 dark:text-slate-600'">#8F9C2A</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <button type="button" @click="toggle()" :aria-label="jugando ? 'Pausar' : 'Reanudar'"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-900 text-white hover:bg-slate-700 dark:bg-cyan-600 dark:hover:bg-cyan-500">
                        <span x-show="!jugando">▶</span><span x-show="jugando" x-cloak>❚❚</span>
                    </button>
                    <button type="button" @click="reiniciar()" aria-label="Reiniciar"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-slate-200 text-slate-500 hover:bg-slate-50 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/5">↺</button>
                    <span class="ml-auto font-mono text-[11px] text-slate-400" x-text="(actual + 1) + '/4 · ' + Math.round(pctGlobal) + '%'"></span>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     PILARES OPERATIVOS
============================================================ --}}
<section id="pilares" class="relative border-y border-slate-200/70 bg-white py-20 dark:border-white/5 dark:bg-night-800/50">
    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <span class="font-mono text-xs font-bold uppercase tracking-[0.25em] text-cyan-600 dark:text-cyan-400">Pilares operativos</span>
            <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Toda la operación bajo un mismo techo</h2>
        </div>
        <div class="mt-12 grid gap-6 md:grid-cols-3">
            @php
                $pilares = [
                    ['🔍', 'Auditoría e integridad de evidencia', 'Cada intervención queda sellada: evidencia fotográfica con GPS, checklist de campo y hash inmutable de cierre.', 'cyan'],
                    ['⏱️', 'Control de tickets y SLA automático', 'Vencimientos calculados por el contrato de cada sitio, alarmas por vencer y paneles con porcentajes en tiempo real.', 'emerald'],
                    ['🚚', 'Despacho de cuadrillas e inventario', 'Asignación por supervisor, seguimiento GPS en vivo, control de insumos y gasto mensual por material.', 'blue'],
                ];
            @endphp
            @foreach ($pilares as [$icono, $titulo, $texto, $acento])
                <div class="group rounded-2xl border border-slate-200 bg-slate-50 p-7 transition hover:-translate-y-1 hover:shadow-xl dark:border-white/10 dark:bg-night-700/60 dark:hover:border-cyan-500/30">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl text-2xl {{ $acento === 'cyan' ? 'bg-cyan-500/10' : ($acento === 'emerald' ? 'bg-emerald-500/10' : 'bg-blue-500/10') }}">{{ $icono }}</div>
                    <h3 class="mt-5 text-lg font-bold text-slate-900 dark:text-white">{{ $titulo }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $texto }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     FLUJO OPERATIVO INTERACTIVO
============================================================ --}}
<section id="flujo" class="relative py-20" x-data="flujoSigo()" x-init="paso = 0">
    <div class="pointer-events-none absolute inset-0 opacity-30 dark:opacity-[0.12]"
         style="background-image:linear-gradient(rgba(148,163,184,.25) 1px,transparent 1px),linear-gradient(90deg,rgba(148,163,184,.25) 1px,transparent 1px);background-size:44px 44px;"></div>
    <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <span class="font-mono text-xs font-bold uppercase tracking-[0.25em] text-cyan-600 dark:text-cyan-400">Flujo operativo</span>
            <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">De la incidencia al cierre auditado</h2>
            <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">Recorré cada etapa con un clic.</p>
        </div>

        <div class="mt-12 grid gap-8 lg:grid-cols-2 lg:items-center">
            <div class="space-y-2">
                <template x-for="(item, i) in pasos" :key="item.titulo">
                    <button type="button" @click="paso = i"
                            class="flex w-full items-center gap-4 rounded-xl border p-4 text-left transition"
                            :class="paso === i ? 'border-cyan-500 bg-cyan-500/10 dark:border-cyan-400 dark:bg-cyan-500/10' : 'border-slate-200 bg-white hover:border-slate-300 dark:border-white/10 dark:bg-night-700/50 dark:hover:border-cyan-500/30'">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full font-mono text-sm font-bold"
                              :class="paso === i ? 'bg-cyan-600 text-white' : 'bg-slate-100 text-slate-500 dark:bg-white/10 dark:text-slate-400'" x-text="i + 1"></span>
                        <span>
                            <span class="block text-sm font-bold text-slate-800 dark:text-white" x-text="item.titulo"></span>
                            <span class="block text-xs text-slate-500 dark:text-slate-400" x-text="item.resumen"></span>
                        </span>
                    </button>
                </template>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-lg dark:border-white/10 dark:bg-night-700/60">
                <template x-for="(item, i) in pasos" :key="'detalle-' + i">
                    <div x-show="paso === i" x-transition.opacity.duration.400>
                        <div class="text-4xl" x-text="item.icono"></div>
                        <h3 class="mt-4 text-xl font-extrabold text-slate-900 dark:text-white" x-text="item.titulo"></h3>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300" x-text="item.detalle"></p>
                        <ul class="mt-5 space-y-2">
                            <template x-for="b in item.bullets" :key="b">
                                <li class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300"><span class="font-bold text-emerald-500">✓</span><span x-text="b"></span></li>
                            </template>
                        </ul>
                    </div>
                </template>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     FOOTER
============================================================ --}}
<footer class="border-t border-slate-200 bg-white py-10 dark:border-white/5 dark:bg-night-800">
    <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 sm:flex-row sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
            <img src="/images/sigo-nexo-mark.svg" alt="SIGO-NEXO" class="h-9 w-9">
            <div>
                <div class="text-sm font-extrabold text-slate-900 dark:text-white">SIGO<span class="text-blue-700">-NEXO</span></div>
                <div class="text-[11px] text-slate-500 dark:text-slate-400">Sistema Integrado de Gestión Operativa</div>
            </div>
        </div>
        <p class="text-center text-xs text-slate-400 dark:text-slate-500">© {{ date('Y') }} Nexo Obras y Servicios · Auditoría de tickets y cuadrilla técnica</p>
    </div>
</footer>
@endsection

@push('scripts')
<script>
function alternarTema() {
    const html = document.documentElement;
    const oscuro = !html.classList.contains('dark');
    html.classList.toggle('dark', oscuro);
    localStorage.setItem('sigo-tema', oscuro ? 'oscuro' : 'claro');
    document.getElementById('icono-tema').textContent = oscuro ? '🌙' : '☀️';
}

function simuladorSigo() {
    return {
        etapas: [
            { codigo: '01', titulo: 'TICKET' }, { codigo: '02', titulo: 'DESPACHO' },
            { codigo: '03', titulo: 'CAMPO' }, { codigo: '04', titulo: 'AUDITORÍA' },
        ],
        protocolo: [
            'Check-in GPS en sitio', 'Foto antes / después (EXIF)',
            'Medición potencia óptica', 'Firma digital & Hash cierre',
        ],
        actual: 0, progreso: 0, jugando: true, timer: null,
        get pctGlobal() { return Math.min(100, ((this.actual * 100) + this.progreso) / 4); },
        get itemsValidados() { return Math.min(4, Math.floor(this.pctGlobal / 25)); },
        get slaRestante() {
            const restante = Math.max(0, Math.floor(24 * 3600 * (1 - this.progreso / 100)));
            const h = String(Math.floor(restante / 3600)).padStart(2, '0');
            const m = String(Math.floor((restante % 3600) / 60)).padStart(2, '0');
            const s = String(restante % 60).padStart(2, '0');
            return `${h}:${m}:${s}`;
        },
        get estadoMapa() {
            return {
                0: 'Ticket #TK-8492 creado · SLA 24 h activo',
                1: 'Cuadrilla Alfa-07 en ruta hacia Nodo Norte-04',
                2: 'Ejecutando protocolo técnico en sitio',
                3: 'Ticket #TK-8492 cerrado y auditado dentro de SLA',
            }[this.actual];
        },
        get xMarcador() { const p = this.actual === 0 ? 0 : (this.actual === 1 ? this.progreso : 100); return -10 + p * 3.1; },
        get yMarcador() { const p = this.actual === 0 ? 0 : (this.actual === 1 ? this.progreso : 100); const t = p / 100; return 250 - 190 * t; },
        iniciar() { const self = this; this.timer = setInterval(() => self.tick(), 70); },
        tick() {
            if (!this.jugando) return;
            this.progreso += 1;
            if (this.progreso >= 100) { this.progreso = 0; this.actual = (this.actual + 1) % 4; }
        },
        toggle() { this.jugando = !this.jugando; },
        reiniciar() { this.actual = 0; this.progreso = 0; this.jugando = true; },
        irA(i) { this.actual = i; this.progreso = 0; this.jugando = true; },
    };
}

function flujoSigo() {
    return {
        paso: 0,
        pasos: [
            { titulo: 'Alta del ticket', resumen: 'Carga manual o desde Excel con validación', icono: '📝',
              detalle: 'El supervisor registra la incidencia o importa el Excel. El sistema detecta re-subidas por hash y solo incorpora tickets nuevos o con cambios.',
              bullets: ['N° de orden + sitio + prioridad', 'SLA según contrato del sitio', 'Validación de duplicados'] },
            { titulo: 'Despacho de cuadrilla', resumen: 'Cada supervisor con sus técnicos', icono: '🚚',
              detalle: 'El supervisor asigna únicamente a los técnicos bajo su supervisión, organizados por zonas delimitadas sobre el mapa.',
              bullets: ['Zonas por sitio', 'Técnicos por supervisor', 'Seguimiento GPS en vivo'] },
            { titulo: 'Ejecución y auditoría', resumen: 'Evidencia en campo, incluso offline', icono: '📷',
              detalle: 'El técnico registra fotos comprimidas con ubicación, valida materiales del inventario y firma la intervención. La app sincroniza al volver la conexión.',
              bullets: ['Fotos con GPS', 'Insumos y stock', 'Modo offline'] },
            { titulo: 'Cierre y control', resumen: 'Sello inmutable y métricas', icono: '🔒',
              detalle: 'El supervisor resuelve y el administrador cierra con sello de auditoría. Paneles en porcentajes y control de gasto por mes supervisan la operación.',
              bullets: ['Historial inmutable', 'Alertas SLA', 'Dashboard y gasto por mes'] },
        ],
    };
}
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const oscuro = document.documentElement.classList.contains('dark');
        const el = document.getElementById('icono-tema');
        if (el) el.textContent = oscuro ? '🌙' : '☀️';
    });
</script>
@endpush
