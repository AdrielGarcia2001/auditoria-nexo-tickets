@extends('layouts.app')

@section('title', 'Mi perfil')

@section('content')
    <div class="mx-auto max-w-2xl">
        <div class="mb-5">
            <h1 class="text-2xl font-bold text-slate-900">👤 Mi perfil</h1>
            <p class="text-sm text-slate-500">
                Tus datos personales, contraseña y (si sos supervisor/admin) la conexión a Google Drive.
            </p>
        </div>

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

        {{-- ============================================================
             DATOS PERSONALES
        ============================================================ --}}
        <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
            <h2 class="font-semibold text-slate-900">Datos personales</h2>

            <form method="POST" action="{{ route('perfil.actualizar') }}" class="mt-4 space-y-4">
                @csrf

                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nombre y apellido</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $usuario->name) }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $usuario->email) }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="telefono" class="mb-1 block text-sm font-medium text-slate-700">
                        Teléfono <span class="text-slate-400">(opcional)</span>
                    </label>
                    <input id="telefono" type="tel" name="telefono" value="{{ old('telefono', $usuario->telefono) }}"
                           inputmode="tel"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    @error('telefono')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-lg bg-slate-50 p-4">
                    <p class="mb-3 text-sm font-medium text-slate-700">Cambiar contraseña (opcional)</p>
                    <div class="space-y-3">
                        <div>
                            <label for="password_actual" class="mb-1 block text-xs font-medium text-slate-600">Contraseña actual</label>
                            <input id="password_actual" type="password" name="password_actual"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                            @error('password_actual')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label for="password" class="mb-1 block text-xs font-medium text-slate-600">Nueva contraseña</label>
                                <input id="password" type="password" name="password"
                                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                                @error('password')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="password_confirmation" class="mb-1 block text-xs font-medium text-slate-600">Repetir nueva</label>
                                <input id="password_confirmation" type="password" name="password_confirmation"
                                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button class="w-full rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 sm:w-auto">
                        💾 Guardar cambios
                    </button>
                </div>
            </form>
        </div>

        {{-- ============================================================
             GOOGLE DRIVE — solo admin/supervisor
        ============================================================ --}}
        @if ($usuario->puedeGestionar())
            <div class="mt-4 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <h2 class="font-semibold text-slate-900">☁️ Google Drive</h2>
                <p class="text-xs text-slate-500">
                    Conectá tu cuenta de Google para que el sistema cree carpetas por ticket y suba las fotos antes/después.
                </p>

                <div class="mt-3 flex flex-wrap items-center gap-3">
                    @if ($usuario->google_refresh_token)
                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-200">
                            ✅ Conectado
                        </span>
                    @else
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-500 ring-1 ring-slate-200">
                            ⚪ No conectado
                        </span>
                    @endif
                </div>

                {{-- form para pegar el token manualmente (alternativa al OAuth) --}}
                <form method="POST" action="{{ route('perfil.google.token') }}" class="mt-4 space-y-2">
                    @csrf
                    <label for="google_refresh_token" class="mb-1 block text-sm font-medium text-slate-700">
                        Refresh token de Google
                    </label>
                    <textarea id="google_refresh_token" name="google_refresh_token" rows="3"
                              placeholder="Pegá acá el refresh_token (obtenido con php artisan google:auth o desde Google OAuth Playground)"
                              class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">{{ old('google_refresh_token', $usuario->google_refresh_token) }}</textarea>
                    @error('google_refresh_token')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <div class="flex flex-wrap gap-2">
                        <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                            💾 Guardar token
                        </button>
                        @if ($usuario->google_refresh_token)
                            <button type="button" onclick="document.getElementById('google_refresh_token').value='';"
                                    class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 ring-1 ring-slate-300 hover:bg-slate-50">
                                Limpiar
                            </button>
                        @endif
                        <a href="{{ route('google.auth') }}"
                           class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-blue-700 ring-1 ring-blue-300 hover:bg-blue-50">
                            O conectar con Google (OAuth)
                        </a>
                    </div>
                </form>

                @if ($usuario->google_refresh_token)
                    <form method="POST" action="{{ route('perfil.google.desconectar') }}" class="mt-2">
                        @csrf
                        <button class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-red-700 ring-1 ring-red-300 hover:bg-red-50">
                            Desconectar Google Drive
                        </button>
                    </form>
                @endif

                @if (! config('google.client_id') || ! config('google.client_secret'))
                    <p class="mt-3 text-xs text-amber-700">
                        ⚠️ Falta configurar <code>GOOGLE_CLIENT_ID</code> y <code>GOOGLE_CLIENT_SECRET</code> en el servidor.
                    </p>
                @endif
            </div>
        @endif
    </div>
@endsection
