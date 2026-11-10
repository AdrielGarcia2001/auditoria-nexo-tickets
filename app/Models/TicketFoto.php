<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ticket_id', 'user_id', 'tipo', 'ruta', 'drive_url', 'lat', 'lng', 'tomada_at'])]
class TicketFoto extends Model
{
    protected $table = 'ticket_fotos';

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'tomada_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
