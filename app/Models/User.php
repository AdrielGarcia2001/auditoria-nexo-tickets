<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'area', 'telefono', 'activo',
    'solicitud_estado', 'solicitud_at', 'aprobado_at', 'aprobado_por',
    'google_refresh_token'])]
#[Hidden(['password', 'remember_token', 'google_refresh_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_SUPERVISOR = 'supervisor';
    public const ROLE_TECNICO = 'tecnico';

    public const AREA_MANTENIMIENTO = 'mantenimiento';
    public const AREA_LIMPIEZA = 'limpieza';

    // ====== estados de la solicitud de registro ======
    public const SOLICITUD_PENDIENTE = 'pendiente';
    public const SOLICITUD_ACEPTADO = 'aceptado';
    public const SOLICITUD_RECHAZADO = 'rechazado';

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
            'ultimo_seen_at' => 'datetime',
            'solicitud_at' => 'datetime',
            'aprobado_at' => 'datetime',
        ];
    }

    // ====== solicitud de registro (aprobacion del admin) ======

    public function solicitudPendiente(): bool
    {
        return $this->solicitud_estado === self::SOLICITUD_PENDIENTE;
    }

    public function solicitudRechazada(): bool
    {
        return $this->solicitud_estado === self::SOLICITUD_RECHAZADO;
    }

    /** El usuario puede entrar: solicitud aceptada y cuenta activa. */
    public function puedeEntrar(): bool
    {
        return $this->solicitud_estado === self::SOLICITUD_ACEPTADO && $this->activo;
    }

    /** Texto amigable del perfil (rol + area). */
    public function perfilLabel(): string
    {
        if ($this->isTecnico()) {
            return 'Técnico de '.($this->area === self::AREA_LIMPIEZA ? 'limpieza' : 'mantenimiento');
        }

        return $this->isAdmin() ? 'Administrador' : 'Supervisor';
    }

    // ====== roles ======

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isSupervisor(): bool
    {
        return $this->role === self::ROLE_SUPERVISOR;
    }

    public function isTecnico(): bool
    {
        return $this->role === self::ROLE_TECNICO;
    }

    /** Admin y supervisor pueden gestionar tickets (el tecnico solo ve los suyos). */
    public function puedeGestionar(): bool
    {
        return $this->isAdmin() || $this->isSupervisor();
    }

    /** Solo el admin cierra y reabre. */
    public function puedeCerrar(): bool
    {
        return $this->isAdmin();
    }

    // ====== relaciones ======

    /** Administrador que acepto la solicitud de registro. */
    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(TicketAsignacion::class, 'tecnico_id');
    }

    public function ticketsAsignados(): HasMany
    {
        return $this->hasManyThrough(
            Ticket::class,
            TicketAsignacion::class,
            'tecnico_id', // clave foranea en ticket_asignaciones
            'id',         // clave foranea en tickets
            'id',         // clave local en users
            'ticket_id'   // clave local en ticket_asignaciones
        );
    }

    public function sitiosFijos(): HasMany
    {
        return $this->hasMany(TecnicoSitio::class, 'tecnico_id');
    }

    public function registrosGps(): HasMany
    {
        return $this->hasMany(RegistroGps::class);
    }
}
