@extends('layouts.app')

@section('title', 'Mapa en vivo')

@section('content')
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">🗺️ Mapa en vivo</h1>
            <p class="text-sm text-slate-500">
                Posición de la cuadrilla técnica en tiempo real (PostGIS + Reverb).
            </p>
        </div>
        <div class="flex gap-2">
            <button id="btn-checkin"
                    class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                ✅ Check-in
            </button>
            <button id="btn-checkout"
                    class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                🏁 Check-out
            </button>
            <button id="btn-tracking"
                    class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                📍 Enviar posición
            </button>
        </div>
    </div>

    <div id="mapa" class="h-[70vh] w-full rounded-xl bg-slate-200 shadow-sm ring-1 ring-slate-200"></div>

    <div id="estado-gps" class="mt-3 text-sm text-slate-500">Esperando ubicación…</div>
    <div id="pendientes-sync" class="mt-1 hidden text-sm font-medium text-amber-700"></div>

    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <h2 class="font-semibold text-slate-900">Técnicos en línea</h2>
            <ul id="lista-tecnicos" class="mt-2 space-y-1 text-sm text-slate-600">
                <li class="text-slate-400">Cargando…</li>
            </ul>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm ring-1 ring-slate-200">
            <h2 class="font-semibold text-slate-900">Km recorridos (hoy)</h2>
            <div id="km-info" class="mt-2 text-sm text-slate-600">—</div>
        </div>
    </div>

    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
              integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
                integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            (function () {
                const map = L.map('mapa').setView([-26.18, -58.17], 12); // Formosa
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap',
                }).addTo(map);

                const markers = {};

                function icono(color) {
                    return L.divIcon({
                        className: '',
                        html: `<div style="background:${color};width:14px;height:14px;border-radius:50%;border:2px solid #fff;box-shadow:0 0 4px rgba(0,0,0,.4)"></div>`,
                        iconSize: [14, 14],
                        iconAnchor: [7, 7],
                    });
                }

                function colorPorTipo(tipo) {
                    if (tipo === 'check_in') return '#16a34a';
                    if (tipo === 'check_out') return '#dc2626';
                    return '#2563eb';
                }

                function ponerMarcador(p) {
                    const key = p.user_id;
                    if (markers[key]) {
                        markers[key].setLatLng([p.lat, p.lng]);
                        markers[key].setIcon(icono(colorPorTipo(p.tipo)));
                        markers[key].bindPopup(`<b>${p.nombre}</b><br>${p.tipo} · ${p.registrado_at ?? ''}`);
                    } else {
                        markers[key] = L.marker([p.lat, p.lng], { icon: icono(colorPorTipo(p.tipo)) })
                            .addTo(map)
                            .bindPopup(`<b>${p.nombre}</b><br>${p.tipo} · ${p.registrado_at ?? ''}`);
                    }
                }

                // carga inicial
                fetch('/gps/posiciones', { headers: { 'Accept': 'application/json' } })
                    .then((r) => r.json())
                    .then((data) => {
                        data.forEach(ponerMarcador);
                        actualizarLista(data);
                    })
                    .catch(() => {});

                function actualizarLista(data) {
                    const ul = document.getElementById('lista-tecnicos');
                    if (!data.length) { ul.innerHTML = '<li class="text-slate-400">Sin posiciones todavía.</li>'; return; }
                    ul.innerHTML = data.map((p) =>
                        `<li><span style="color:${colorPorTipo(p.tipo)}">●</span> ${p.nombre} <span class="text-xs text-slate-400">(${p.tipo}, ${p.registrado_at ?? ''})</span></li>`
                    ).join('');
                }

                // tiempo real via Reverb
                if (window.Echo) {
                    window.Echo.channel('mapa.vivo')
                        .listen('.posicion.nueva', (e) => {
                            ponerMarcador(e);
                            // refresca lista simple
                            fetch('/gps/posiciones').then((r) => r.json()).then(actualizarLista).catch(() => {});
                        });
                }

                // helpers GPS
                function obtenerPosicion() {
                    return new Promise((resolve, reject) => {
                        if (!navigator.geolocation) { reject(new Error('Geolocalización no soportada')); return; }
                        navigator.geolocation.getCurrentPosition(
                            (pos) => resolve(pos),
                            (err) => reject(err),
                            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                        );
                    });
                }

                async function enviar(tipo, url) {
                    const estado = document.getElementById('estado-gps');
                    estado.textContent = 'Obteniendo ubicación…';
                    try {
                        const pos = await obtenerPosicion();
                        const body = {
                            lat: pos.coords.latitude,
                            lng: pos.coords.longitude,
                            precision_m: pos.coords.accuracy,
                            velocidad_kmh: pos.coords.speed ? pos.coords.speed * 3.6 : null,
                        };
                        const headers = {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        };
                        const opciones = { method: 'POST', headers, body: JSON.stringify(body) };

                        let json;
                        if (window.nexoOffline && !navigator.onLine) {
                            const r = await window.nexoOffline.fetchConFallbackOffline(url, opciones, tipo + ' GPS');
                            json = r;
                            estado.textContent = `⏳ ${tipo} guardado offline (se sincronizará al volver la señal)`;
                        } else {
                            const resp = await fetch(url, opciones);
                            json = await resp.json();
                            if (!resp.ok) throw new Error(json.message || 'Error');
                            estado.textContent = `✅ ${tipo} registrado (${body.lat.toFixed(5)}, ${body.lng.toFixed(5)})`;
                            cargarKm();
                        }
                    } catch (e) {
                        estado.textContent = '❌ ' + (e.message || e);
                    }
                }

                document.getElementById('btn-checkin').addEventListener('click', () => enviar('Check-in', '/gps/check-in'));
                document.getElementById('btn-checkout').addEventListener('click', () => enviar('Check-out', '/gps/check-out'));
                document.getElementById('btn-tracking').addEventListener('click', () => enviar('Posición', '/gps/tracking'));

                const userId = {{ auth()->id() }};
                async function cargarKm() {
                    try {
                        const hoy = new Date().toISOString().slice(0, 10);
                        const r = await fetch(`/gps/km/${userId}?desde=${hoy}&hasta=${hoy}`, { headers: { 'Accept': 'application/json' } });
                        const j = await r.json();
                        document.getElementById('km-info').textContent = `${j.nombre}: ${j.km} km recorridos hoy.`;
                    } catch (e) {
                        document.getElementById('km-info').textContent = 'No se pudo calcular.';
                    }
                }
                cargarKm();
            })();
        </script>
    @endpush
@endsection
