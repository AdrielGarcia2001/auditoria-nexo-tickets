<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Crear cuenta · Auditoría de Tickets Nexo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-slate-900 p-4">
    <div class="w-full max-w-md">
        <div class="mb-6 text-center text-white">
            <div class="text-4xl">🎫</div>
            <h1 class="mt-2 text-xl font-semibold">Crear cuenta</h1>
            <p class="text-sm text-slate-400">Elegí tu perfil: el administrador debe aceptarte antes de poder ingresar.</p>
        </div>

        @if (session('exito'))
            <div class="mb-4 rounded-lg border border-emerald-300/60 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 shadow">
                ✅ {{ session('exito') }}
            </div>
        @endif

        <form method="POST" action="{{ route('registro.post') }}" class="space-y-4 rounded-xl bg-white p-6 shadow-xl">
            @csrf

            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nombre y apellido</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                @error('name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Contraseña</label>
                    <input id="password" type="password" name="password" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Repetir contraseña</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                </div>
            </div>

            <div>
                <label for="telefono" class="mb-1 block text-sm font-medium text-slate-700">Teléfono <span class="text-slate-400">(opcional)</span></label>
                <input id="telefono" type="text" name="telefono" value="{{ old('telefono') }}"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                @error('telefono')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- ====== eleccion de perfil ====== --}}
            <fieldset>
                <legend class="mb-2 block text-sm font-medium text-slate-700">¿Con qué perfil te registrás?</legend>

                @php($perfilViejo = old('perfil'))
                <div class="space-y-2">
                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-300 p-3 hover:bg-slate-50 has-[:checked]:border-slate-900 has-[:checked]:bg-slate-50">
                        <input type="radio" name="perfil" value="supervisor"
                               {{ $perfilViejo === 'supervisor' ? 'checked' : '' }} required
                               class="mt-0.5 h-4 w-4 border-slate-300 text-slate-900 focus:ring-slate-900">
                        <span>
                            <span class="block text-sm font-semibold text-slate-900">👔 Supervisor</span>
                            <span class="block text-xs text-slate-500">Importa el Excel, asigna técnicos y resuelve tickets.</span>
                        </span>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-300 p-3 hover:bg-slate-50 has-[:checked]:border-slate-900 has-[:checked]:bg-slate-50">
                        <input type="radio" name="perfil" value="tecnico-mantenimiento"
                               {{ $perfilViejo === 'tecnico-mantenimiento' ? 'checked' : '' }}
                               class="mt-0.5 h-4 w-4 border-slate-300 text-slate-900 focus:ring-slate-900">
                        <span>
                            <span class="block text-sm font-semibold text-slate-900">🔧 Técnico de mantenimiento</span>
                            <span class="block text-xs text-slate-500">Sitios fijos, máx. 2 técnicos por ticket.</span>
                        </span>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-300 p-3 hover:bg-slate-50 has-[:checked]:border-slate-900 has-[:checked]:bg-slate-50">
                        <input type="radio" name="perfil" value="tecnico-limpieza"
                               {{ $perfilViejo === 'tecnico-limpieza' ? 'checked' : '' }}
                               class="mt-0.5 h-4 w-4 border-slate-300 text-slate-900 focus:ring-slate-900">
                        <span>
                            <span class="block text-sm font-semibold text-slate-900">🧹 Técnico de limpieza</span>
                            <span class="block text-xs text-slate-500">1 técnico por ticket, hoja LIMP.</span>
                        </span>
                    </label>
                </div>
                @error('perfil')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </fieldset>

            <button class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">
                Solicitar registro
            </button>

            <p class="text-center text-xs text-slate-500">
                Tu cuenta queda <strong>pendiente</strong> hasta que el administrador la acepte.
            </p>
        </form>

        <p class="mt-4 text-center text-sm text-slate-400">
            ¿Ya tenés cuenta?
            <a href="{{ route('login') }}" class="font-semibold text-white hover:underline">Ingresá acá</a>
        </p>
    </div>
</body>
</html>
