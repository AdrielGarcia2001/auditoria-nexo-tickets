@extends('layouts.app')

@section('title', 'Importar Excel')

@section('content')
    <div class="mb-5">
        <h1 class="text-2xl font-bold text-slate-900">Importar Excel</h1>
        <p class="text-sm text-slate-500">
            Subí el archivo <code class="rounded bg-slate-100 px-1">.xlsx</code> de tickets (o el export de Google Sheets),
            revisá el preview completo y confirmá la importación.
        </p>
    </div>

    <livewire:importador-excel />
@endsection
