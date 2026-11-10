@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
    <div class="mb-5">
        <h1 class="text-2xl font-bold text-slate-900">Usuarios</h1>
        <p class="text-sm text-slate-500">
            Solicitudes de registro pendientes y cuentas del sistema.
            Una cuenta nueva <strong>no puede entrar</strong> hasta que la aceptes.
        </p>
    </div>

    {{-- ============================================================
         SOLICITUDES PENDIENTES
    ============================================================ --}}
    <div class="mb-6 rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-slate-900">📥 Solicitudes pendientes</h2>
            <span class="rounded-full {{ $pendientes->isEmpty() ? 'bg-slate-100 text-slate-500 ring-slate-200' : 'bg-amber-100 text-amber-700 ring-amber-200' }}
                        px-3 py-1 text-sm font-medium ring-1">
                {{ $pendientes->count() }}
            </span>
        </div>

        @if ($pendientes->isEmpty())
            <p class="mt-3 text-sm text-slate-400">No hay solicitudes esperando aprobación. 🎉</p>
        @else
            <ul class="mt-4 space-y-3">
                @foreach ($pendientes as $solicitud)
                    <li class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-900">
                                    {{ $solicitud->name }}
                                    <span class="ml-2 rounded-full bg-white px-2 py-0.5 text-xs font-medium text-slate-600 ring-1 ring-slate-200">
                                        {{ $solicitud->perfilLabel() }}
                                    </span>
                                </p>
                                <p class="text-sm text-slate-600">{{ $solicitud->email }}</p>
                                @if ($solicitud->telefono)
                                    <p class="text-sm text-slate-500">📞 {{ $solicitud->telefono }}</p>
                                @endif
                                <p class="mt-1 text-xs text-slate-400">
                                    Solicitó el {{ $solicitud->solicitud_at?->format('d/m/Y H:i') }}
                                </p>
                            </div>

                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('usuarios.aceptar', $solicitud) }}">
                                    @csrf
                                    <button class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                                        ✅ Aceptar
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('usuarios.rechazar', $solicitud) }}">
                                    @csrf
                                    <button class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-red-700 ring-1 ring-red-300 hover:bg-red-50">
                                        ❌ Rechazar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- ============================================================
         CUENTAS
    ============================================================ --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="font-semibold text-slate-900">Cuentas</h2>
            <p class="text-sm text-slate-500">Activá o desactivá cuentas existentes.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Usuario</th>
                        <th class="px-4 py-3">Perfil</th>
                        <th class="px-4 py-3">Solicitud</th>
                        <th class="px-4 py-3">Aprobada</th>
                        <th class="px-4 py-3">Cuenta</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($usuarios as $cuenta)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900">{{ $cuenta->name }}</div>
                                <div class="text-xs text-slate-400">{{ $cuenta->email }}</div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                                {{ $cuenta->perfilLabel() }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @if ($cuenta->solicitud_estado === \App\Models\User::SOLICITUD_ACEPTADO)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 ring-1 ring-emerald-200">aceptada</span>
                                @elseif ($cuenta->solicitud_estado === \App\Models\User::SOLICITUD_RECHAZADO)
                                    <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 ring-1 ring-red-200">rechazada</span>
                                @else
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 ring-1 ring-amber-200">pendiente</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-500">
                                {{ $cuenta->aprobado_at?->format('d/m/Y H:i') ?? '—' }}
                                @if ($cuenta->aprobador)
                                    <div class="text-[11px] text-slate-400">por {{ $cuenta->aprobador->name }}</div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @if ($cuenta->activo)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">activa</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500 ring-1 ring-slate-200">inactiva</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                @if ($cuenta->id !== auth()->id())
                                    <form method="POST" action="{{ route('usuarios.alternar', $cuenta) }}">
                                        @csrf
                                        <button class="rounded-md px-3 py-1.5 text-xs font-semibold ring-1 transition
                                                     {{ $cuenta->activo
                                                        ? 'bg-white text-slate-600 ring-slate-300 hover:bg-slate-50'
                                                        : 'bg-slate-900 text-white ring-slate-900 hover:bg-slate-700' }}">
                                            {{ $cuenta->activo ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-slate-300">tu cuenta</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-400">
                                No hay cuentas todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
