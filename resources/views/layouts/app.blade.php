<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icon-192.png">
    <title>@yield('title', 'Auditoría de Tickets') · Nexo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body class="h-full bg-slate-100 text-slate-800 antialiased">
    <div class="min-h-full flex flex-col">
        {{-- barra superior (mobile-first) --}}
        <header class="sticky top-0 z-40 bg-slate-900 text-white shadow">
            <div class="mx-auto flex h-14 max-w-7xl items-center justify-between gap-3 px-3 sm:px-6">
                <div class="flex items-center gap-3">
                    <button type="button" id="btn-menu"
                            class="rounded-md p-2 text-slate-300 hover:bg-slate-800 lg:hidden"
                            aria-label="Abrir menú" onclick="document.getElementById('menu-lateral').classList.toggle('hidden')">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <a href="{{ route('dashboard') }}" class="font-semibold tracking-tight">
                        🎫 Nexo <span class="hidden sm:inline text-slate-400 font-normal">· Auditoría de Tickets</span>
                    </a>
                </div>

                <div class="flex items-center gap-3 text-sm">
                    @auth
                        <span class="hidden sm:inline text-slate-300">
                            {{ auth()->user()->name }}
                            <span class="ml-1 rounded-full bg-slate-700 px-2 py-0.5 text-xs uppercase">{{ auth()->user()->role }}</span>
                        </span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="rounded-md bg-slate-800 px-3 py-1.5 text-slate-200 hover:bg-slate-700">Salir</button>
                        </form>
                    @endauth
                </div>
            </div>
        </header>

        <div class="mx-auto flex w-full max-w-7xl flex-1 gap-0 px-0 sm:px-6">
            {{-- menu lateral --}}
            <aside id="menu-lateral"
                   class="hidden w-56 shrink-0 border-r border-slate-200 bg-white p-4 lg:block">
                @include('partials.nav')
            </aside>

            {{-- contenido --}}
            <main class="min-w-0 flex-1 p-3 sm:p-6">
                @if (session('exito'))
                    <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        {{ session('exito') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>

        <footer class="border-t border-slate-200 bg-white py-3 text-center text-xs text-slate-500">
            Nexo Obras y Servicios · Sistema de auditoría de tickets y cuadrilla técnica
        </footer>
    </div>

    @livewireScripts
    @stack('scripts')
    <script>
        // registra el service worker de la PWA (offline + background sync)
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch((e) => console.warn('SW no registrado', e));
            });
        }
    </script>
</body>
</html>
