<!DOCTYPE html>
<html lang="es" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#070b14">
    <meta name="description" content="SIGO-NEXO · Sistema Integrado de Gestión Operativa. Auditoría en tiempo real, control de tickets con SLA y gestión de cuadrillas técnicas en campo.">
    <title>@yield('title', 'SIGO-NEXO · Gestión Operativa')</title>
    <link rel="icon" type="image/svg+xml" href="/images/sigo-nexo-mark.svg">
    <link rel="apple-touch-icon" href="/icon-192.png">
    <script>
        (function () {
            var t = localStorage.getItem('sigo-tema') || 'oscuro';
            if (t === 'oscuro') document.documentElement.classList.add('dark');
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body class="min-h-full bg-white text-slate-800 antialiased dark:bg-night-900 dark:text-slate-200">
    @yield('content')
    @livewireScripts
    @stack('scripts')
</body>
</html>
