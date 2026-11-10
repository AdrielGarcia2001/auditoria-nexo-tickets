<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sitio_id', 'nombre', 'descripcion', 'horas_por_prioridad', 'activo'])]
class ContratoSla extends Model
{
    protected $table = 'contratos_sla';

    protected $casts = [
        'horas_por_prioridad' => 'array',
        'activo' => 'boolean',
    ];

    public function sitio(): BelongsTo
    {
        return $this->belongsTo(Sitio::class);
    }

    /** Horas objetivo para una prioridad (override del contrato o el default del sistema). */
    public function horasPara(?string $prioridad): ?int
    {
        if (! $prioridad || ! is_array($this->horas_por_prioridad)) {
            return Ticket::horasParaPrioridad($prioridad);
        }

        return $this->horas_por_prioridad[trim($prioridad)]
            ?? Ticket::horasParaPrioridad($prioridad);
    }
}
