<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ticket_id', 'tipo', 'mensaje', 'leida_at'])]
class AlertaSla extends Model
{
    protected $table = 'alertas_sla';

    public const TIPO_POR_VENCER = 'por_vencer';
    public const TIPO_VENCIDO = 'vencido';

    protected $casts = ['leida_at' => 'datetime'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
