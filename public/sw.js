/**
 * Service Worker PWA - Auditoria de Tickets Nexo
 *
 * - App shell cacheada: la app abre sin internet (offline)
 * - Cola offline (Background Sync): check-ins, fotos y comentarios
 *   se guardan en IndexedDB y se sincronizan al volver la señal.
 * - Tiles de OpenStreetMap con cache runtime (mapa offline).
 */

const VERSION = 'nexo-v1';
const APP_SHELL = [
    '/',
    '/dashboard',
    '/manifest.json',
    '/icon-192.png',
    '/icon-512.png',
];

// =========================================================
//  INSTALACION
// =========================================================
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(VERSION)
            .then((cache) => cache.addAll(APP_SHELL))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((k) => k !== VERSION).map((k) => caches.delete(k))
            ))
            .then(() => self.clients.claim())
    );
});

// =========================================================
//  FETCH
// =========================================================
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);

    // solo GET; los POST (livewire /api) se manejan aparte
    if (event.request.method !== 'GET') {
        return;
    }

    // --- tiles de OpenStreetMap: cache primero (mapa offline) ---
    if (url.hostname.endsWith('.tile.openstreetmap.org')) {
        event.respondWith(
            caches.match(event.request).then((hit) =>
                hit || fetch(event.request).then((resp) => {
                    const copy = resp.clone();
                    caches.open(VERSION).then((c) => c.put(event.request, copy));
                    return resp;
                })
            )
        );
        return;
    }

    // --- paginas: red primero, luego cache (para abrir sin internet) ---
    if (event.request.mode === 'navigate') {
        event.respondWith(
            fetch(event.request)
                .then((resp) => {
                    const copy = resp.clone();
                    caches.open(VERSION).then((c) => c.put(event.request, copy));
                    return resp;
                })
                .catch(() =>
                    caches.match(event.request).then((hit) => hit || caches.match('/dashboard'))
                )
        );
        return;
    }

    // --- assets de vite: cache primero ---
    if (url.pathname.startsWith('/build/') || url.pathname.startsWith('/assets/')) {
        event.respondWith(
            caches.match(event.request).then((hit) =>
                hit || fetch(event.request).then((resp) => {
                    const copy = resp.clone();
                    caches.open(VERSION).then((c) => c.put(event.request, copy));
                    return resp;
                })
            )
        );
    }
});

// =========================================================
//  BACKGROUND SYNC: sincroniza la cola offline al volver la red
// =========================================================
self.addEventListener('sync', (event) => {
    if (event.tag === 'nexo-sync') {
        event.waitUntil(sincronizarCola());
    }
});

async function sincronizarCola() {
    const db = await abrirDb();
    const cola = await leerTodo(db, 'cola_offline');

    for (const item of cola) {
        try {
            const resp = await fetch(item.url, {
                method: item.method || 'POST',
                headers: item.headers || {},
                body: item.body || null,
            });
            if (resp.ok) {
                await borrar(db, item.id);
            }
        } catch (e) {
            // sigue sin red: se reintenta en el proximo sync
        }
    }
}

function abrirDb() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open('nexo-offline', 1);
        req.onupgradeneeded = () => {
            const db = req.result;
            if (!db.objectStoreNames.contains('cola_offline')) {
                db.createObjectStore('cola_offline', { keyPath: 'id', autoIncrement: true });
            }
        };
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

function leerTodo(db, store) {
    return new Promise((resolve, reject) => {
        const tx = db.transaction(store, 'readonly');
        const req = tx.objectStore(store).getAll();
        req.onsuccess = () => resolve(req.result || []);
        req.onerror = () => reject(req.error);
    });
}

function borrar(db, id) {
    return new Promise((resolve, reject) => {
        const tx = db.transaction('cola_offline', 'readwrite');
        tx.objectStore('cola_offline').delete(id);
        tx.oncomplete = () => resolve();
        tx.onerror = () => reject(tx.error);
    });
}
