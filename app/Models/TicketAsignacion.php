<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ticket_id', 'tecnico_id', 'asignado_por'])]
class TicketAsignacion extends Model
{
    protected $table = 'ticket_asignaciones';

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tecnico_id');
    }

    public function asignadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'asignado_por');
    }
}
