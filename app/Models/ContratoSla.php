<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sitio_id', 'nombre', 'descripcion', 'horas_por_prioridad', 'activo'])]
class ContratoSla extends Model
{
    protected $table = 'contratos_sla';

    /** Horas disponibles para seleccionar en el contrato. */
    public const HORAS_OPCIONES = [1, 2, 4, 6, 8, 12, 16, 24, 36, 48, 72, 96, 120, 144, 168, 240, 360, 720];

    protected $casts = [
        'horas_por_prioridad' => 'array',
        'activo' => 'boolean',
    ];

    public function sitio(): BelongsTo
    {
        return $this->belongsTo(Sitio::class);
    }

    /**
     * Horas objetivo para una prioridad según el contrato.
     * Solo las prioridades marcadas (checkbox) tienen SLA; las demás devuelven null.
     */
    public function horasPara(?string $prioridad): ?int
    {
        if (! $prioridad) {
            return null;
        }

        if (! is_array($this->horas_por_prioridad)) {
            return Ticket::horasParaPrioridad($prioridad);
        }

        $horas = $this->horas_por_prioridad[trim($prioridad)] ?? null;

        return $horas === null || $horas === '' ? null : (int) $horas;
    }

    /** Prioridades marcadas en el contrato. */
    public function prioridadesMarcadas(): array
    {
        return is_array($this->horas_por_prioridad)
            ? array_keys($this->horas_por_prioridad)
            : [];
    }
}
