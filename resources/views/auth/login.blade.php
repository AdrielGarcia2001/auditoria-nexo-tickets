<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">
    <link rel="manifest" href="/manifest.json">
    <title>Ingresar · Auditoría de Tickets Nexo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-slate-900 p-4">
    <div class="w-full max-w-sm">
        <div class="mb-6 text-center text-white">
            <img src="/images/sigo-nexo-mark.svg" alt="SIGO-NEXO" class="mx-auto h-14 w-14">
            <h1 class="mt-2 text-xl font-semibold">SIGO<span class="text-blue-400">-NEXO</span> <span class="text-slate-400 font-normal">· Auditoría de Tickets</span></h1>
            <p class="text-sm text-slate-400">Cuadrilla técnica y control en tiempo real</p>
        </div>

        @if (session('exito'))
            <div class="mb-4 rounded-lg border border-emerald-300/60 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 shadow">
                ✅ {{ session('exito') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4 rounded-xl bg-white p-6 shadow-xl">
            @csrf

            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Contraseña</label>
                <input id="password" type="password" name="password" required
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" class="rounded border-slate-300">
                Recordarme
            </label>

            <button class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">
                Ingresar
            </button>
        </form>

        <p class="mt-4 text-center text-xs text-slate-500">
            Si no tenés cuenta, contactá al administrador del sistema.
        </p>
        <p class="mt-3 text-center text-xs text-slate-500">
            <a href="{{ route('landing') }}" class="hover:underline">← Volver al inicio</a>
        </p>
    </div>
</body>
</html>
