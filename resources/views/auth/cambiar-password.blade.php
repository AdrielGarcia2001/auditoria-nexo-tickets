<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f172a">
    <link rel="manifest" href="/manifest.json">
    <title>Cambiar contraseña · Nexo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-slate-900 p-4">
    <div class="w-full max-w-sm">
        <div class="mb-6 text-center text-white">
            <div class="text-4xl">🔐</div>
            <h1 class="mt-2 text-xl font-semibold">Cambiar contraseña</h1>
            <p class="text-sm text-slate-400">Por seguridad, definí tu nueva contraseña.</p>
        </div>

        @if (session('error'))
            <div class="mb-4 rounded-lg border border-red-300/60 bg-red-50 px-4 py-3 text-sm font-medium text-red-900 shadow">
                ❌ {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.cambiar') }}" class="space-y-4 rounded-xl bg-white p-6 shadow-xl">
            @csrf

            <div>
                <label for="password_actual" class="mb-1 block text-sm font-medium text-slate-700">Contraseña actual</label>
                <input id="password_actual" type="password" name="password_actual" required
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                @error('password_actual')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Nueva contraseña</label>
                <input id="password" type="password" name="password" required
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Repetir nueva contraseña</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
            </div>

            <button class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">
                Cambiar contraseña
            </button>
        </form>

        <p class="mt-4 text-center text-xs text-slate-500">¿Problemas? Contactá al administrador del sistema.</p>
    </div>
</body>
</html>
