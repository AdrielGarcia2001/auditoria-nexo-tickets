<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'area', 'telefono', 'activo',
    'password_cambiado', 'google_refresh_token'])]
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

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
            'ultimo_seen_at' => 'datetime',
        ];
    }

    /** El usuario puede entrar: cuenta activa. */
    public function puedeEntrar(): bool
    {
        return $this->activo;
    }

    /** Si es true, al loguearse debe cambiar la contraseña obligatoriamente. */
    public function necesitaCambiarPassword(): bool
    {
        return ! $this->password_cambiado;
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

    // ====== supervisor <-> tecnicos ======

    /** Relación directa con la tabla supervisor_tecnicos (solo si es supervisor). */
    public function tecnicosAsignados(): HasMany
    {
        return $this->hasMany(SupervisorTecnico::class, 'supervisor_id');
    }

    /** IDs de los técnicos bajo este supervisor. */
    public function tecnicoIds(): array
    {
        if (! $this->isSupervisor()) {
            return [];
        }

        return $this->tecnicosAsignados()
            ->pluck('tecnico_id')
            ->all();
    }

    /** Técnicos activos bajo este supervisor. */
    public function tecnicos(): HasMany
    {
        return $this->hasMany(User::class)
            ->whereIn('id', $this->tecnicoIds())
            ->where('role', self::ROLE_TECNICO)
            ->where('activo', true)
            ->orderBy('name');
    }

    /** Verifica si un técnico pertenece a este supervisor. */
    public function tieneTecnico(User $tecnico): bool
    {
        if (! $this->isSupervisor()) {
            return false;
        }

        return SupervisorTecnico::query()
            ->where('supervisor_id', $this->id)
            ->where('tecnico_id', $tecnico->id)
            ->exists();
    }

    // ====== supervisor <-> zonas ======

    /** Relación directa con la tabla supervisor_zonas (solo si es supervisor). */
    public function zonasAsignadas(): HasMany
    {
        return $this->hasMany(SupervisorZona::class, 'supervisor_id');
    }

    /** IDs de las zonas asignadas a este supervisor. */
    public function zonaIds(): array
    {
        if (! $this->isSupervisor()) {
            return [];
        }

        return $this->zonasAsignadas()
            ->pluck('zona_id')
            ->all();
    }

    /** Zonas activas asignadas a este supervisor. */
    public function zonas(): HasMany
    {
        return $this->hasMany(Zona::class)
            ->whereIn('id', $this->zonaIds())
            ->where('activo', true)
            ->orderBy('nombre');
    }

    /** Verifica si este supervisor gestiona una zona. */
    public function tieneZona(Zona $zona): bool
    {
        if (! $this->isSupervisor()) {
            return false;
        }

        return SupervisorZona::query()
            ->where('supervisor_id', $this->id)
            ->where('zona_id', $zona->id)
            ->exists();
    }
}
