<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use App\Services\Tickets\FlujoTicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN, 'activo' => true, 'solicitud_estado' => User::SOLICITUD_ACEPTADO]);
    }

    private function supervisor(): User
    {
        return User::factory()->create(['role' => User::ROLE_SUPERVISOR, 'activo' => true, 'solicitud_estado' => User::SOLICITUD_ACEPTADO]);
    }

    private function tecnico(): User
    {
        return User::factory()->create(['role' => User::ROLE_TECNICO, 'area' => User::AREA_MANTENIMIENTO, 'activo' => true, 'solicitud_estado' => User::SOLICITUD_ACEPTADO]);
    }

    private function ticketNuevo(): Ticket
    {
        return Ticket::create([
            'numero_orden' => 'T-1',
            'clase_orden' => 'FSMA',
            'estado_workflow' => Ticket::NUEVO,
            'fecha_entrada' => now()->toDateString(),
            'prioridad' => '24 horas',
        ]);
    }

    public function test_supervisor_resuelve_ticket_nuevo(): void
    {
        $sup = $this->supervisor();
        $ticket = $this->ticketNuevo();

        app(FlujoTicketService::class)->resolver($ticket, $sup, 'resuelto ok');

        $ticket->refresh();
        $this->assertSame(Ticket::RESUELTO, $ticket->estado_workflow);
        $this->assertSame($sup->id, $ticket->resuelto_por);
    }

    public function test_no_se_puede_resolver_ticket_resuelto(): void
    {
        $sup = $this->supervisor();
        $ticket = $this->ticketNuevo();
        app(FlujoTicketService::class)->resolver($ticket, $sup);

        $this->expectException(\LogicException::class);
        app(FlujoTicketService::class)->resolver($ticket->refresh(), $sup);
    }

    public function test_admin_cierra_ticket_resuelto(): void
    {
        $admin = $this->admin();
        $sup = $this->supervisor();
        $ticket = $this->ticketNuevo();
        app(FlujoTicketService::class)->resolver($ticket, $sup);

        app(FlujoTicketService::class)->cerrar($ticket->refresh(), $admin, 'cierre');

        $ticket->refresh();
        $this->assertSame(Ticket::CERRADO, $ticket->estado_workflow);
        $this->assertSame($admin->id, $ticket->cerrado_por);
    }

    public function test_solo_admin_puede_cerrar(): void
    {
        $sup = $this->supervisor();
        $ticket = $this->ticketNuevo();
        app(FlujoTicketService::class)->resolver($ticket, $sup);

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        app(FlujoTicketService::class)->cerrar($ticket->refresh(), $sup);
    }

    public function test_admin_reabre_y_convierte_a_correctivo(): void
    {
        $admin = $this->admin();
        $sup = $this->supervisor();
        $ticket = $this->ticketNuevo();
        $ticket->tipo = 'preventivo';
        $ticket->save();
        app(FlujoTicketService::class)->resolver($ticket, $sup);

        app(FlujoTicketService::class)->reabrir($ticket->refresh(), $admin, true, 'motivo');

        $ticket->refresh();
        $this->assertSame(Ticket::NUEVO, $ticket->estado_workflow);
        $this->assertTrue($ticket->reabierto);
        $this->assertSame('correctivo', $ticket->tipo);
    }

    public function test_limite_tecnicos_limpieza(): void
    {
        $sup = $this->supervisor();
        $limpio = Ticket::create([
            'numero_orden' => 'L-1',
            'clase_orden' => 'FSMA',
            'estado_workflow' => Ticket::NUEVO,
            'fecha_entrada' => now()->toDateString(),
            'origen_hoja' => 'LIMP (M01PR012)',
        ]);
        $t1 = $this->tecnico();
        $t2 = $this->tecnico();

        $this->expectException(\LogicException::class);
        app(FlujoTicketService::class)->asignar($limpio, [$t1->id, $t2->id], $sup);
    }

    public function test_roles_helpers(): void
    {
        $admin = $this->admin();
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->puedeGestionar());
        $this->assertTrue($admin->puedeCerrar());

        $tec = $this->tecnico();
        $this->assertTrue($tec->isTecnico());
        $this->assertFalse($tec->puedeGestionar());
        $this->assertFalse($tec->puedeCerrar());
    }
}
