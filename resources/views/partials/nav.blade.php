@php($u = auth()->user())

<nav class="space-y-1 text-sm">
    <a href="{{ route('dashboard') }}"
       class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('dashboard') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
        📊 Dashboard
    </a>

    <a href="{{ route('tickets.index') }}"
       class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('tickets.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
        🎫 Tickets
    </a>

    <a href="{{ route('mapa.index') }}"
       class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('mapa.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
        🗺️ Mapa en vivo
    </a>

    @if ($u && $u->puedeGestionar())
        <div class="pt-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Gestión</div>

        <a href="{{ route('importar.index') }}"
           class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('importar.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
            📥 Importar Excel
        </a>

        <a href="{{ route('sitios.index') }}"
           class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('sitios.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
            🏢 Sitios
        </a>
    @endif

    <div class="pt-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Cuenta</div>

    <a href="{{ route('perfil.index') }}"
       class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('perfil.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
        👤 Mi perfil
    </a>

    @if ($u && $u->isAdmin())
        <div class="pt-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Administración</div>

        <a href="{{ route('usuarios.index') }}"
           class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('usuarios.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
            👥 Usuarios
        </a>

        <a href="{{ route('alertas.index') }}"
           class="block rounded-md px-3 py-2 font-medium {{ request()->routeIs('alertas.*') ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
            ⏰ Alertas SLA
        </a>
    @endif
</nav>
