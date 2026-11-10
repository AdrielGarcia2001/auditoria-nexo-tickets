@php
    $estilos = [
        'nuevo' => ['bg-blue-100 text-blue-700 ring-blue-200', 'Nuevo'],
        'resuelto' => ['bg-amber-100 text-amber-700 ring-amber-200', 'Resuelto'],
        'cerrado' => ['bg-emerald-100 text-emerald-700 ring-emerald-200', 'Cerrado'],
    ];
    [$clase, $texto] = $estilos[$estado] ?? ['bg-slate-100 text-slate-500 ring-slate-200', $estado];
@endphp

<span class="inline-flex whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-semibold ring-1 {{ $clase }}">
    {{ $texto }}
</span>
