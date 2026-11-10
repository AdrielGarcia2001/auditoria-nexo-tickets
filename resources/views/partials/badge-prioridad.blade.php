@php
    $colores = [
        '24 horas' => 'bg-red-100 text-red-700 ring-red-200',
        '48 horas' => 'bg-orange-100 text-orange-700 ring-orange-200',
        '72 horas' => 'bg-amber-100 text-amber-700 ring-amber-200',
        '7 Días' => 'bg-yellow-100 text-yellow-700 ring-yellow-200',
        '15 Días' => 'bg-blue-100 text-blue-700 ring-blue-200',
        '30 Días' => 'bg-indigo-100 text-indigo-700 ring-indigo-200',
        'Programado Mensual' => 'bg-slate-100 text-slate-600 ring-slate-200',
    ];
    $clave = trim((string) $prioridad);
@endphp

<span class="inline-flex whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-medium ring-1
             {{ $colores[$clave] ?? 'bg-slate-100 text-slate-500 ring-slate-200' }}">
    {{ $prioridad ?: 'sin prioridad' }}
</span>
