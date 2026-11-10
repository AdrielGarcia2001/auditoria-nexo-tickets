<?php

namespace App\Http\Controllers;

use App\Events\NuevaPosicionGps;
use App\Models\RegistroGps;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Check-in / check-out / tracking GPS de la cuadrilla tecnica.
 * Almacena en PostGIS y emite el evento NuevaPosicionGps por Reverb.
 */
class GpsController extends Controller
{
    public function checkIn(Request $request): JsonResponse
    {
        return $this->registrar($request, 'check_in');
    }

    public function checkOut(Request $request): JsonResponse
    {
        return $this->registrar($request, 'check_out');
    }

    public function tracking(Request $request): JsonResponse
    {
        return $this->registrar($request, 'tracking');
    }

    private function registrar(Request $request, string $tipo): JsonResponse
    {
        $datos = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'precision_m' => ['nullable', 'numeric', 'min:0'],
            'velocidad_kmh' => ['nullable', 'numeric', 'min:0'],
            'ticket_id' => ['nullable', 'integer', 'exists:tickets,id'],
        ]);

        $usuario = Auth::user();

        $registro = RegistroGps::create([
            'user_id' => $usuario->id,
            'ticket_id' => $datos['ticket_id'] ?? null,
            'tipo' => $tipo,
            'lat' => $datos['lat'],
            'lng' => $datos['lng'],
            'precision_m' => $datos['precision_m'] ?? null,
            'velocidad_kmh' => $datos['velocidad_kmh'] ?? null,
            'registrado_at' => now(),
        ]);

        // actualiza ultima posicion conocida del usuario (para el mapa sin consultar GPS)
        $usuario->update([
            'ultimo_lat' => $datos['lat'],
            'ultimo_lng' => $datos['lng'],
            'ultimo_seen_at' => now(),
        ]);

        event(new NuevaPosicionGps(
            userId: $usuario->id,
            nombre: $usuario->name,
            area: $usuario->area,
            lat: (float) $datos['lat'],
            lng: (float) $datos['lng'],
            ticketId: $datos['ticket_id'] ?? null,
            tipo: $tipo,
            registradoAt: $registro->registrado_at?->toIso8601String(),
        ));

        return response()->json([
            'ok' => true,
            'id' => $registro->id,
            'tipo' => $tipo,
            'registrado_at' => $registro->registrado_at,
        ]);
    }

    /** Ultimas posiciones de todos los tecnicos (carga inicial del mapa). */
    public function posiciones(): JsonResponse
    {
        $ultimas = RegistroGps::ultimasPosiciones();

        $usuarios = User::whereIn('id', collect($ultimas)->pluck('user_id'))
            ->get(['id', 'name', 'area', 'role'])
            ->keyBy('id');

        $resultado = collect($ultimas)->map(function ($p) use ($usuarios) {
            $u = $usuarios->get($p->user_id);

            return [
                'user_id' => $p->user_id,
                'nombre' => $u?->name ?? 'Técnico #'.$p->user_id,
                'area' => $u?->area,
                'lat' => (float) $p->lat,
                'lng' => (float) $p->lng,
                'precision_m' => $p->precision_m,
                'tipo' => $p->tipo,
                'ticket_id' => $p->ticket_id,
                'registrado_at' => $p->registrado_at,
            ];
        })->values();

        return response()->json($resultado);
    }

    /** Recorrido reciente de un tecnico (para pintar la polilinea). */
    public function recorrido(User $user): JsonResponse
    {
        $puntos = RegistroGps::recorridoReciente($user->id, 500);

        return response()->json(collect($puntos)->map(fn ($p) => [
            'lat' => (float) $p->lat,
            'lng' => (float) $p->lng,
            'tipo' => $p->tipo,
            'registrado_at' => $p->registrado_at,
        ]));
    }

    /** Km recorridos por un tecnico entre dos fechas. */
    public function km(Request $request, User $user): JsonResponse
    {
        $datos = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date'],
        ]);

        $desde = $datos['desde'] ?? now()->startOfDay();
        $hasta = $datos['hasta'] ?? now()->endOfDay();

        $km = RegistroGps::kmRecorridos($user->id, $desde, $hasta);

        return response()->json([
            'user_id' => $user->id,
            'nombre' => $user->name,
            'desde' => $desde,
            'hasta' => $hasta,
            'km' => $km,
        ]);
    }
}
