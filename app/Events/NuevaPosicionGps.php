<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Se dispara cada vez que un tecnico registra una posicion GPS.
 * El mapa en vivo se suscribe al canal publico "mapa.vivo".
 */
class NuevaPosicionGps implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public string $nombre,
        public ?string $area = null,
        public float $lat = 0.0,
        public float $lng = 0.0,
        public ?int $ticketId = null,
        public string $tipo = 'tracking',
        public ?string $registradoAt = null,
    ) {
    }

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [new Channel('mapa.vivo')];
    }

    public function broadcastAs(): string
    {
        return 'posicion.nueva';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'nombre' => $this->nombre,
            'area' => $this->area,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'ticket_id' => $this->ticketId,
            'tipo' => $this->tipo,
            'registrado_at' => $this->registradoAt,
        ];
    }
}
