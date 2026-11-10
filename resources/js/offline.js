// ====== Cola offline (IndexedDB) para la cuadrilla tecnica ======
// Guarda acciones (check-in, check-out, tracking, comentarios) cuando no hay red
// y las sincroniza al volver la conexion (Background Sync + reintento manual).

const DB_NAME = 'nexo-offline';
const STORE = 'cola_offline';

function abrirDb() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open(DB_NAME, 1);
        req.onupgradeneeded = () => {
            const db = req.result;
            if (!db.objectStoreNames.contains(STORE)) {
                db.createObjectStore(STORE, { keyPath: 'id', autoIncrement: true });
            }
        };
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

async function encolar(accion) {
    const db = await abrirDb();
    return new Promise((resolve, reject) => {
        const tx = db.transaction(STORE, 'readwrite');
        const req = tx.objectStore(STORE).add({
            url: accion.url,
            method: accion.method || 'POST',
            headers: accion.headers || {},
            body: accion.body || null,
            creado_at: new Date().toISOString(),
            descripcion: accion.descripcion || accion.url,
        });
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}

async function contarPendientes() {
    const db = await abrirDb();
    return new Promise((resolve) => {
        const tx = db.transaction(STORE, 'readonly');
        const req = tx.objectStore(STORE).count();
        req.onsuccess = () => resolve(req.result || 0);
        req.onerror = () => resolve(0);
    });
}

async function sincronizarManual() {
    const db = await abrirDb();
    const items = await new Promise((resolve) => {
        const tx = db.transaction(STORE, 'readonly');
        const req = tx.objectStore(STORE).getAll();
        req.onsuccess = () => resolve(req.result || []);
        req.onerror = () => resolve([]);
    });

    let ok = 0;
    for (const item of items) {
        try {
            const resp = await fetch(item.url, {
                method: item.method || 'POST',
                headers: item.headers || {},
                body: item.body || null,
            });
            if (resp.ok) {
                const tx2 = db.transaction(STORE, 'readwrite');
                tx2.objectStore(STORE).delete(item.id);
                ok++;
            }
        } catch (e) {
            // sigue offline
        }
    }

    actualizarIndicador();
    return ok;
}

function actualizarIndicador() {
    contarPendientes().then((n) => {
        const el = document.getElementById('pendientes-sync');
        if (el) {
            el.textContent = n > 0
                ? `⏳ ${n} acción(es) pendiente(s) de sincronización`
                : '';
            el.classList.toggle('hidden', n === 0);
        }
    });
}

// wrapper de fetch para acciones GPS: si no hay red, encola
async function fetchConFallbackOffline(url, opciones, descripcion) {
    const headers = Object.assign({ 'Content-Type': 'application/json' }, opciones.headers || {});

    if (!navigator.onLine) {
        await encolar({
            url,
            method: opciones.method || 'POST',
            headers,
            body: opciones.body || null,
            descripcion,
        });
        if ('serviceWorker' in navigator && 'SyncManager' in window) {
            const reg = await navigator.serviceWorker.ready;
            try { await reg.sync.register('nexo-sync'); } catch (e) { /* ignore */ }
        }
        actualizarIndicador();
        return { ok: true, offline: true };
    }

    try {
        const resp = await fetch(url, opciones);
        if (!resp.ok) throw new Error('HTTP ' + resp.status);
        return await resp.json();
    } catch (e) {
        // red cayo durante el request: encolar
        await encolar({
            url,
            method: opciones.method || 'POST',
            headers,
            body: opciones.body || null,
            descripcion,
        });
        actualizarIndicador();
        return { ok: true, offline: true };
    }
}

// expone globalmente
window.nexoOffline = { encolar, contarPendientes, sincronizarManual, fetchConFallbackOffline, actualizarIndicador };

// al cargar, actualiza indicador y reintenta sync
document.addEventListener('DOMContentLoaded', () => {
    actualizarIndicador();
    if (navigator.onLine) sincronizarManual();
});

window.addEventListener('online', () => {
    sincronizarManual();
});
