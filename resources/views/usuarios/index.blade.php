@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Usuarios</h1>
            <p class="text-sm text-slate-500">
                Cuentas del sistema. El administrador crea cada cuenta con contraseña aleatoria;
                el usuario la cambia al primer ingreso.
            </p>
        </div>

        <a href="{{ route('usuarios.crear') }}"
           class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
            👤 Crear usuario
        </a>
    </div>

    {{-- ============================================================
         CUENTAS
    ============================================================ --}}
    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-slate-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Usuario</th>
                        <th class="px-4 py-3">Perfil</th>
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
                                @if ($cuenta->activo)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">activa</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500 ring-1 ring-slate-200">inactiva</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                @if ($cuenta->isSupervisor())
                                    <a href="{{ route('usuarios.zonas', $cuenta) }}"
                                       class="rounded-md bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 ring-1 ring-blue-200 hover:bg-blue-100">
                                        🗺️ Zonas y técnicos
                                    </a>
                                @endif
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
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-400">
                                No hay cuentas todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
