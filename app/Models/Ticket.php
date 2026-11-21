<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    // campos del Excel
    'mda', 'numero_orden', 'clase_orden', 'fecha_entrada', 'fecha_modificacion_maestro',
    'grupo_planificador', 'texto_breve', 'descripcion', 'calle', 'ubicacion', 'sitio_id',
    'provincia_codigo', 'provincia_nombre', 'anio', 'area_empresa', 'doc_adjunto',
    'inicio_extrema', 'fin_extrema', 'estatus', 'prioridad', 'estado', 'lo_resolvio',
    'fecha_resolucion', 'documentacion', 'comentarios', 'fotos_texto', 'comentarios_extra',
    // workflow del sistema
    'tipo', 'reabierto', 'estado_workflow', 'resuelto_por', 'resuelto_at',
    'cerrado_por', 'cerrado_at', 'drive_folder_url',
    // sla
    'sla_vencimiento', 'sla_alertado_at',
    // trazabilidad
    'origen_hoja', 'fila_origen', 'importado_por', 'importado_at',
])]
class Ticket extends Model
{
    protected $table = 'tickets';

    public const NUEVO = 'nuevo';
    public const RESUELTO = 'resuelto';
    public const CERRADO = 'cerrado';

    public const PRIORIDAD_HORAS = [
        '24 horas' => 24,
        '48 horas' => 48,
        '72 horas' => 72,
        '7 Días' => 168,
        '15 Días' => 360,
        '30 Días' => 720,
    ];

    protected $casts = [
        'fecha_entrada' => 'date',
        'fecha_modificacion_maestro' => 'date',
        'inicio_extrema' => 'date',
        'fin_extrema' => 'date',
        'fecha_resolucion' => 'date',
        'reabierto' => 'boolean',
        'resuelto_at' => 'datetime',
        'cerrado_at' => 'datetime',
        'sla_vencimiento' => 'datetime',
        'sla_alertado_at' => 'datetime',
        'importado_at' => 'datetime',
        'anio' => 'integer',
        'fila_origen' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Ticket $t) {
            if (! $t->tipo) {
                $t->tipo = $t->clase_orden === 'FSMC' ? 'preventivo' : 'correctivo';
            }
        });

        static::saving(function (Ticket $t) {
            $t->calcularSlaVencimiento();
        });
    }

    // ====== relaciones ======

    public function sitio(): BelongsTo
    {
        return $this->belongsTo(Sitio::class);
    }

    public function resueltoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resuelto_por');
    }

    public function cerradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrado_por');
    }

    public function importadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'importado_por');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(TicketAsignacion::class);
    }

    public function tecnicos(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'ticket_asignaciones', 'ticket_id', 'tecnico_id')
            ->withPivot('asignado_por')
            ->withTimestamps();
    }

    /** Comentarios del sistema (usuarios). */
    public function comentariosSistema(): HasMany
    {
        return $this->hasMany(TicketComentario::class)->orderByDesc('created_at');
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(TicketFoto::class);
    }

    public function historial(): HasMany
    {
        return $this->hasMany(TicketHistorial::class)->orderByDesc('created_at');
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(AlertaSla::class);
    }

    public function consumibles(): HasMany
    {
        return $this->hasMany(ConsumibleTicket::class);
    }

    // ====== workflow ======

    public function esNuevo(): bool
    {
        return $this->estado_workflow === self::NUEVO;
    }

    public function esResuelto(): bool
    {
        return $this->estado_workflow === self::RESUELTO;
    }

    public function esCerrado(): bool
    {
        return $this->estado_workflow === self::CERRADO;
    }

    public function puedeResolverse(): bool
    {
        return $this->esNuevo();
    }

    public function puedeCerrarse(): bool
    {
        return $this->esResuelto();
    }

    public function puedeReabrirse(): bool
    {
        return $this->esResuelto();
    }

    // ====== area del ticket ======

    public function area(): string
    {
        if ($this->origen_hoja === 'LIMP (M01PR012)' || $this->grupo_planificador === 'M02') {
            return 'limpieza';
        }

        return 'mantenimiento';
    }

    public function limiteTecnicos(): int
    {
        return $this->area() === 'limpieza' ? 1 : 2;
    }

    // ====== SLA ======

    public static function horasParaPrioridad(?string $prioridad): ?int
    {
        if (! $prioridad) {
            return null;
        }

        return self::PRIORIDAD_HORAS[trim($prioridad)] ?? null;
    }

    /** Recalcula sla_vencimiento = fecha_entrada + horas del contrato del sitio (o default). */
    public function calcularSlaVencimiento(): void
    {
        $horas = $this->horasSlaParaTicket();

        if (! $this->fecha_entrada || $horas === null) {
            $this->sla_vencimiento = null;

            return;
        }

        $this->sla_vencimiento = $this->fecha_entrada->copy()->addHours($horas)->endOfDay();
    }

    /** Horas de SLA efectivas: contrato activo del sitio (null = sin SLA) o default. */
    public function horasSlaParaTicket(): ?int
    {
        if ($this->sitio_id) {
            $contrato = \App\Models\ContratoSla::where('sitio_id', $this->sitio_id)
                ->where('activo', true)
                ->first();

            if ($contrato) {
                return $contrato->horasPara($this->prioridad);
            }
        }

        return self::horasParaPrioridad($this->prioridad);
    }

    public function slaVencido(): bool
    {
        return $this->sla_vencimiento !== null
            && $this->sla_vencimiento->isPast()
            && ! $this->esCerrado();
    }

    public function slaPorVencer(): bool
    {
        return $this->sla_vencimiento !== null
            && ! $this->slaVencido()
            && $this->sla_vencimiento->diffInHours(now()) <= 24
            && $this->sla_vencimiento->isFuture()
            && ! $this->esCerrado();
    }

    // ====== scopes ======

    public function scopeWorkflow(Builder $q, string $estado): Builder
    {
        return $q->where('estado_workflow', $estado);
    }

    public function scopeDelTecnico(Builder $q, User $user): Builder
    {
        return $q->whereHas('tecnicos', fn ($t) => $t->whereKey($user->id));
    }

    /**
     * Filtra tickets según el rol del usuario (única fuente de verdad).
     * Admin: todos | Supervisor: sus zonas | Técnico: sus tickets.
     */
    public function scopePorRol(Builder $q, User $user): Builder
    {
        if ($user->isTecnico()) {
            $q->delTecnico($user);
        }

        if ($user->isSupervisor()) {
            $q->whereHas('sitio.zona', fn ($z) => $z->whereIn('id', $user->zonaIds()));
        }

        return $q;
    }

    public function scopePendientes(Builder $q): Builder
    {
        return $q->whereIn('estado_workflow', [self::NUEVO, self::RESUELTO]);
    }
}
