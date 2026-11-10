<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

#[Fillable(['user_id', 'ticket_id', 'tipo', 'lat', 'lng', 'precision_m', 'velocidad_kmh', 'registrado_at'])]
class RegistroGps extends Model
{
    protected $table = 'registros_gps';

    protected $casts = [
        'lat' => 'float',
        'lng' => 'float',
        'precision_m' => 'float',
        'velocidad_kmh' => 'float',
        'registrado_at' => 'datetime',
    ];

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    // ====== consultas PostGIS ======

    /** Ultimos puntos de todos los tecnicos (para el mapa en vivo). */
    public static function ultimasPosiciones(): array
    {
        return DB::select(<<<'SQL'
            SELECT DISTINCT ON (user_id)
                user_id, ticket_id, tipo, lat, lng, precision_m, registrado_at
            FROM registros_gps
            ORDER BY user_id, registrado_at DESC
        SQL);
    }

    /**
     * Km recorridos por un usuario entre dos fechas (PostGIS, en metros geograficos).
     * Suma la distancia entre puntos consecutivos de la misma fecha.
     */
    public static function kmRecorridos(int $userId, $desde, $hasta): float
    {
        $resultado = DB::selectOne(<<<'SQL'
            WITH puntos AS (
                SELECT punto, registrado_at,
                       LAG(punto) OVER (ORDER BY registrado_at) AS anterior
                FROM registros_gps
                WHERE user_id = ?
                  AND registrado_at BETWEEN ? AND ?
                  AND tipo IN ('check_in', 'check_out', 'tracking')
            )
            SELECT COALESCE(SUM(
                CASE WHEN anterior IS NOT NULL
                     THEN ST_Distance(punto::geography, anterior::geography)
                     ELSE 0 END
            ), 0) AS metros
            FROM puntos
        SQL, [$userId, $desde, $hasta]);

        return round(($resultado->metros ?? 0) / 1000, 2);
    }

    /** Ultimos N km de un usuario (para pintar el recorrido en el mapa). */
    public static function recorridoReciente(int $userId, int $limite = 500): array
    {
        return DB::select(<<<'SQL'
            SELECT lat, lng, tipo, registrado_at
            FROM registros_gps
            WHERE user_id = ?
            ORDER BY registrado_at DESC
            LIMIT ?
        SQL, [$userId, $limite]);
    }

    public function scopeDeTicket(Builder $q, int $ticketId): Builder
    {
        return $q->where('ticket_id', $ticketId);
    }
}
