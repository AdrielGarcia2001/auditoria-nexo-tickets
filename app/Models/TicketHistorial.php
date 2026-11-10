<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketHistorial extends Model
{
    protected $table = 'ticket_historial';

    public $timestamps = false;

    protected $casts = [
        'detalles' => 'array',
        'created_at' => 'datetime',
    ];

    protected $fillable = ['ticket_id', 'user_id', 'accion', 'desde', 'hasta', 'detalles'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
