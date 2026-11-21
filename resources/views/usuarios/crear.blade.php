@extends('layouts.app')

@section('title', 'Crear usuario')

@section('content')
    <div class="mb-5">
        <a href="{{ route('usuarios.index') }}" class="text-sm text-slate-400 hover:text-slate-600">← Volver a usuarios</a>
        <h1 class="text-2xl font-bold text-slate-900 mt-1">Crear usuario</h1>
        <p class="text-sm text-slate-500">El administrador crea la cuenta con una contraseña aleatoria. El usuario deberá cambiarla al primer ingreso.</p>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <form method="POST" action="{{ route('usuarios.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nombre y apellido</label>
                    <input id="name" type="text" name="name" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <input id="email" type="email" name="email" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="role" class="mb-1 block text-sm font-medium text-slate-700">Rol</label>
                    <select id="role" name="role" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="admin">Administrador</option>
                        <option value="supervisor">Supervisor</option>
                        <option value="tecnico">Técnico</option>
                    </select>
                </div>
                <div>
                    <label for="area" class="mb-1 block text-sm font-medium text-slate-700">Área (solo técnicos)</label>
                    <select id="area" name="area" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">— sin área —</option>
                        <option value="mantenimiento">Mantenimiento</option>
                        <option value="limpieza">Limpieza</option>
                    </select>
                </div>
                <div>
                    <label for="telefono" class="mb-1 block text-sm font-medium text-slate-700">Teléfono (opcional)</label>
                    <input id="telefono" type="tel" name="telefono" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-slate-200 flex justify-end">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">
                    👤 Crear usuario
                </button>
            </div>
        </form>
    </div>
@endsection
