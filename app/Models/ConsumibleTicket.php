<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ticket_id', 'articulo_id', 'cantidad_utilizada', 'precio_unitario_momento', 'registrado_por'])]
class ConsumibleTicket extends Model
{
    protected $table = 'consumibles_ticket';

    protected $casts = [
        'precio_unitario_momento' => 'decimal:2',
        'consumido_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function articulo(): BelongsTo
    {
        return $this->belongsTo(ArticuloInventario::class, 'articulo_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    /** Gasto de este consumible = cantidad * precio al momento. */
    public function getGastoAttribute(): float
    {
        return (float) $this->cantidad_utilizada * (float) $this->precio_unitario_momento;
    }
}
