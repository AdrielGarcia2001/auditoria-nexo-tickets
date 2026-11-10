@extends('layouts.app')

@section('title', 'Ticket #' . $ticket->numero_orden)

@section('content')
    <livewire:detalle-ticket :ticketId="$ticket->id" :key="'detalle-' . $ticket->id" />
@endsection
