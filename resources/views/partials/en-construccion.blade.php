@extends('layouts.app')

@section('title', $titulo)

@section('content')
    <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
        <h1 class="text-xl font-bold text-slate-900">{{ $titulo }}</h1>
        <p class="mt-2 text-sm text-slate-500">🚧 Sección en construcción: se está implementando en esta fase.</p>
        @isset($nota)
            <p class="mt-1 text-sm text-slate-600">{{ $nota }}</p>
        @endisset
    </div>
@endsection
