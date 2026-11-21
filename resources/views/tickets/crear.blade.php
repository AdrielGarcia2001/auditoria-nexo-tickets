@extends('layouts.app')

@section('title', 'Nuevo ticket')

@section('content')
    <div class="mb-5">
        <a href="{{ route('tickets.index') }}" class="text-sm text-slate-400 hover:text-slate-600">← Volver a tickets</a>
        <h1 class="text-2xl font-bold text-slate-900 mt-1">📝 Nuevo ticket</h1>
        <p class="text-sm text-slate-500">Registrá un ticket manualmente. Los campos marcados con * son obligatorios.</p>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
        <form method="POST" action="{{ route('tickets.crear.store') }}" class="space-y-5">
            @csrf

            <h2 class="text-lg font-semibold text-slate-900 border-b border-slate-200 pb-2 mb-4">📋 Datos del ticket</h2>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label for="numero_orden" class="mb-1 block text-sm font-medium text-slate-700">N° de orden <span class="text-red-500">*</span></label>
                    <input id="numero_orden" type="text" name="numero_orden" value="{{ old('numero_orden') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-200">
                    @error('numero_orden') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="clase_orden" class="mb-1 block text-sm font-medium text-slate-700">Clase de orden <span class="text-red-500">*</span></label>
                    <select id="clase_orden" name="clase_orden" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="FSMA">FSMA (Correctivo)</option>
                        <option value="FSMC">FSMC (Preventivo)</option>
                    </select>
                    @error('clase_orden') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="fecha_entrada" class="mb-1 block text-sm font-medium text-slate-700">Fecha de entrada <span class="text-red-500">*</span></label>
                    <input id="fecha_entrada" type="date" name="fecha_entrada" value="{{ old('fecha_entrada') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @error('fecha_entrada') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="texto_breve" class="mb-1 block text-sm font-medium text-slate-700">Texto breve <span class="text-red-500">*</span></label>
                    <input id="texto_breve" type="text" name="texto_breve" value="{{ old('texto_breve') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @error('texto_breve') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="ubicacion" class="mb-1 block text-sm font-medium text-slate-700">Ubicación <span class="text-red-500">*</span></label>
                    <input id="ubicacion" type="text" name="ubicacion" value="{{ old('ubicacion') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @error('ubicacion') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="prioridad" class="mb-1 block text-sm font-medium text-slate-700">Prioridad <span class="text-red-500">*</span></label>
                    <select id="prioridad" name="prioridad" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @foreach ($prioridades as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                        @endforeach
                    </select>
                    @error('prioridad') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="provincia_codigo" class="mb-1 block text-sm font-medium text-slate-700">Provincia <span class="text-red-500">*</span></label>
                    <select id="provincia_codigo" name="provincia_codigo" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="A36">FORMOSA (A36)</option>
                        <option value="A32">CHACO (A32)</option>
                        <option value="A33">CORRIENTES (A33)</option>
                        <option value="A34">ENTRE RÍOS (A34)</option>
                        <option value="A35">MISIONES (A35)</option>
                    </select>
                    @error('provincia_codigo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="provincia_nombre" class="mb-1 block text-sm font-medium text-slate-700">Nombre provincia <span class="text-red-500">*</span></label>
                    <input id="provincia_nombre" type="text" name="provincia_nombre" value="{{ old('provincia_nombre', 'FORMOSA') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @error('provincia_nombre') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <label for="descripcion" class="mb-1 block text-sm font-medium text-slate-700">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('descripcion') }}</textarea>
                </div>
            </div>

            <h2 class="text-lg font-semibold text-slate-900 border-b border-slate-200 pb-2 mb-4 mt-6">🔧 Datos adicionales (opcionales)</h2>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['mda','MDA','text'], ['grupo_planificador','Grupo planificador','text'],
                    ['calle','Calle','text'], ['anio','Año','number'], ['area_empresa','Área de empresa','text'],
                    ['estatus','Estatus','text'], ['estado','Estado (Excel)','text'],
                    ['lo_resolvio','Lo resolvió','text'], ['fecha_resolucion','Fecha resolución','date'],
                ] as [$campo, $etiqueta, $tipo])
                    <div>
                        <label for="{{ $campo }}" class="mb-1 block text-sm font-medium text-slate-700">{{ $etiqueta }}</label>
                        <input id="{{ $campo }}" type="{{ $tipo }}" name="{{ $campo }}" value="{{ old($campo) }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                @endforeach
                <div>
                    <label for="sitio_id" class="mb-1 block text-sm font-medium text-slate-700">Sitio</label>
                    <select id="sitio_id" name="sitio_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        <option value="">— sin sitio —</option>
                        @foreach (\App\Models\Sitio::orderBy('nombre')->get() as $sitio)
                            <option value="{{ $sitio->id }}">{{ $sitio->nombre }} ({{ $sitio->provincia_nombre }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <label for="comentarios" class="mb-1 block text-sm font-medium text-slate-700">Comentarios (resolución)</label>
                    <textarea id="comentarios" name="comentarios" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('comentarios') }}</textarea>
                </div>
                <div class="sm:col-span-2 lg:col-span-3">
                    <label for="comentarios_extra" class="mb-1 block text-sm font-medium text-slate-700">Comentarios extra</label>
                    <textarea id="comentarios_extra" name="comentarios_extra" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('comentarios_extra') }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="rounded-lg bg-slate-900 px-6 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">
                    💾 Crear ticket
                </button>
            </div>
        </form>
    </div>
@endsection
