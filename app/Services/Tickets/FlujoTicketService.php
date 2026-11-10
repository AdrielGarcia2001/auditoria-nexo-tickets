<?php

namespace App\Services\Tickets;

use App\Models\Ticket;
use App\Models\TicketComentario;
use App\Models\TicketHistorial;
use App\Models\User;
use App\Services\Google\GoogleDriveService;
use Illuminate\Auth\Access\AuthorizationException;

/**
 * Transiciones de estado del ticket (unicas fuentes de verdad del workflow):
 *
 *   Supervisor:  Nuevo     -> Resuelto   (solo si esta Nuevo)
 *   Admin:       Resuelto  -> Cerrado
 *   Admin:       Resuelto  -> Nuevo      (reabrir; puede cambiar preventivo -> correctivo)
 */
class FlujoTicketService
{
    public function __construct(private GoogleDriveService $drive)
    {
    }

    // ======================================================================
    //  SUPERVISOR (o admin): resolver
    // ======================================================================

    public function resolver(Ticket $ticket, User $user, ?string $comentario = null): Ticket
    {
        if (! $user->puedeGestionar()) {
            throw new AuthorizationException('Solo supervisores o administradores pueden resolver tickets.');
        }

        if (! $ticket->puedeResolverse()) {
            throw new \LogicException('El ticket debe estar en estado Nuevo para poder resolverlo.');
        }

        $ticket->estado_workflow = Ticket::RESUELTO;
        $ticket->resuelto_por = $user->id;
        $ticket->resuelto_at = now();

        // carpeta por N° de ticket en Google Drive (con comentario/descripcion)
        $urlCarpeta = $this->drive->crearCarpetaTicket($ticket, $comentario);
        if ($urlCarpeta) {
            $ticket->drive_folder_url = $urlCarpeta;
        }

        $ticket->save();

        if ($comentario) {
            TicketComentario::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'tipo' => 'resolucion',
                'texto' => $comentario,
            ]);
        }

        TicketHistorial::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'accion' => 'resuelto',
            'desde' => Ticket::NUEVO,
            'hasta' => Ticket::RESUELTO,
            'detalles' => ['carpeta_drive' => $urlCarpeta],
        ]);

        return $ticket;
    }

    // ======================================================================
    //  ADMIN: cerrar
    // ======================================================================

    public function cerrar(Ticket $ticket, User $user, ?string $comentario = null): Ticket
    {
        if (! $user->isAdmin()) {
            throw new AuthorizationException('Solo el administrador puede cerrar tickets.');
        }

        if (! $ticket->puedeCerrarse()) {
            throw new \LogicException('Solo se puede cerrar un ticket que este Resuelto.');
        }

        $ticket->estado_workflow = Ticket::CERRADO;
        $ticket->cerrado_por = $user->id;
        $ticket->cerrado_at = now();
        $ticket->save();

        if ($comentario) {
            TicketComentario::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'tipo' => 'cierre',
                'texto' => $comentario,
            ]);
        }

        TicketHistorial::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'accion' => 'cerrado',
            'desde' => Ticket::RESUELTO,
            'hasta' => Ticket::CERRADO,
        ]);

        return $ticket;
    }

    // ======================================================================
    //  ADMIN: reabrir (Resuelto -> Nuevo, puede cambiar preventivo a correctivo)
    // ======================================================================

    public function reabrir(Ticket $ticket, User $user, bool $convertirACorrectivo = false, ?string $motivo = null): Ticket
    {
        if (! $user->isAdmin()) {
            throw new AuthorizationException('Solo el administrador puede reabrir tickets.');
        }

        if (! $ticket->puedeReabrirse()) {
            throw new \LogicException('Solo se puede reabrir un ticket que este Resuelto.');
        }

        $tipoAnterior = $ticket->tipo;

        $ticket->estado_workflow = Ticket::NUEVO;
        $ticket->reabierto = true;
        $ticket->resuelto_por = null;
        $ticket->resuelto_at = null;

        if ($convertirACorrectivo && $ticket->tipo === 'preventivo') {
            $ticket->tipo = 'correctivo';
        }

        $ticket->save();

        if ($motivo) {
            TicketComentario::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'tipo' => 'reapertura',
                'texto' => $motivo,
            ]);
        }

        TicketHistorial::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'accion' => 'reabierto',
            'desde' => Ticket::RESUELTO,
            'hasta' => Ticket::NUEVO,
            'detalles' => [
                'tipo_anterior' => $tipoAnterior,
                'tipo_nuevo' => $ticket->tipo,
                'convertido_a_correctivo' => $convertirACorrectivo,
            ],
        ]);

        return $ticket;
    }

    // ======================================================================
    //  SUPERVISOR (o admin): asignar tecnicos
    //  mantenimiento: hasta 2 | limpieza: 1
    // ======================================================================

    /**
     * @param  array<int, int>  $tecnicoIds
     */
    public function asignar(Ticket $ticket, array $tecnicoIds, User $user): Ticket
    {
        if (! $user->puedeGestionar()) {
            throw new AuthorizationException('Solo supervisores o administradores pueden asignar técnicos.');
        }

        $tecnicoIds = array_values(array_unique(array_map('intval', $tecnicoIds)));
        $limite = $ticket->limiteTecnicos();

        if (count($tecnicoIds) > $limite) {
            throw new \LogicException(
                "Un ticket de {$ticket->area()} admite como máximo {$limite} técnico(s)."
            );
        }

        $tecnicos = User::whereIn('id', $tecnicoIds)
            ->where('role', User::ROLE_TECNICO)
            ->where('activo', true)
            ->get();

        if ($tecnicos->count() !== count($tecnicoIds)) {
            throw new \LogicException('Alguno de los técnicos seleccionados no existe o no está activo.');
        }

        $antes = $ticket->tecnicos()->pluck('users.id')->all();

        $ticket->tecnicos()->sync(
            collect($tecnicoIds)->mapWithKeys(fn ($id) => [$id => ['asignado_por' => $user->id]])->all()
        );

        TicketHistorial::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'accion' => 'asignado',
            'desde' => implode(',', $antes) ?: null,
            'hasta' => implode(',', $tecnicoIds) ?: null,
            'detalles' => ['tecnicos' => $tecnicos->pluck('name')->all()],
        ]);

        return $ticket->refresh();
    }
}
