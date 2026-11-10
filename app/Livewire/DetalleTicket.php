<?php

namespace App\Livewire;

use App\Models\Ticket;
use App\Models\TicketComentario;
use App\Models\TicketFoto;
use App\Models\TicketHistorial;
use App\Models\User;
use App\Services\Google\GoogleDriveService;
use App\Services\Tickets\FlujoTicketService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Ficha completa del ticket: campos exactos del Excel + workflow
 * (supervisor resuelve, admin cierra/reabre), asignacion, fotos y comentarios.
 */
class DetalleTicket extends Component
{
    use WithFileUploads;

    public int $ticketId;

    // ====== acciones ======
    public bool $mostrarResolver = false;
    public string $comentarioResolucion = '';

    public bool $mostrarCerrar = false;
    public string $comentarioCierre = '';

    public bool $mostrarReabrir = false;
    public string $motivoReapertura = '';
    public bool $convertirACorrectivo = false;

    public bool $mostrarAsignar = false;
    /** @var array<int, int> */
    public array $tecnicosSeleccionados = [];

    // ====== edicion de datos del Excel (solo admin) ======
    public bool $mostrarEditar = false;
    public string $edit_texto_breve = '';
    public string $edit_descripcion = '';
    public string $edit_ubicacion = '';
    public string $edit_calle = '';
    public string $edit_prioridad = '';
    public string $edit_estado = '';
    public string $edit_estatus = '';
    public string $edit_comentarios = '';
    public string $edit_comentarios_extra = '';
    public string $edit_doc_adjunto = '';
    public string $edit_lo_resolvio = '';

    public string $nuevoComentario = '';

    // ====== fotos ======
    public ?\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $foto = null;
    public string $tipoFoto = 'antes';

    public ?string $exito = null;
    public ?string $errorAccion = null;

    public function mount(int $ticketId): void
    {
        $this->ticketId = $ticketId;
        $this->tecnicosSeleccionados = $this->ticket()->tecnicos()->pluck('users.id')->all();
    }

    /** Abre el modal de edicion con los valores actuales del ticket (solo admin). */
    public function abrirEditar(): void
    {
        if (! Auth::user()?->isAdmin()) {
            $this->errorAccion = 'Solo el administrador puede editar los datos del ticket.';

            return;
        }

        $t = $this->ticket();
        $this->edit_texto_breve = (string) ($t->texto_breve ?? '');
        $this->edit_descripcion = (string) ($t->descripcion ?? '');
        $this->edit_ubicacion = (string) ($t->ubicacion ?? '');
        $this->edit_calle = (string) ($t->calle ?? '');
        $this->edit_prioridad = (string) ($t->prioridad ?? '');
        $this->edit_estado = (string) ($t->estado ?? '');
        $this->edit_estatus = (string) ($t->estatus ?? '');
        $this->edit_comentarios = (string) ($t->comentarios ?? '');
        $this->edit_comentarios_extra = (string) ($t->comentarios_extra ?? '');
        $this->edit_doc_adjunto = (string) ($t->doc_adjunto ?? '');
        $this->edit_lo_resolvio = (string) ($t->lo_resolvio ?? '');
        $this->mostrarEditar = true;
        $this->errorAccion = null;
    }

    /** Guarda la edicion del admin y registra en el historial. */
    public function guardarEdicion(): void
    {
        if (! Auth::user()?->isAdmin()) {
            $this->errorAccion = 'Solo el administrador puede editar los datos del ticket.';

            return;
        }

        $t = $this->ticket();

        $antes = [
            'texto_breve' => $t->texto_breve,
            'descripcion' => $t->descripcion,
            'ubicacion' => $t->ubicacion,
            'calle' => $t->calle,
            'prioridad' => $t->prioridad,
            'estado' => $t->estado,
            'estatus' => $t->estatus,
            'comentarios' => $t->comentarios,
            'comentarios_extra' => $t->comentarios_extra,
            'doc_adjunto' => $t->doc_adjunto,
            'lo_resolvio' => $t->lo_resolvio,
        ];

        $t->fill([
            'texto_breve' => $this->edit_texto_breve !== '' ? $this->edit_texto_breve : null,
            'descripcion' => $this->edit_descripcion !== '' ? $this->edit_descripcion : null,
            'ubicacion' => $this->edit_ubicacion !== '' ? $this->edit_ubicacion : null,
            'calle' => $this->edit_calle !== '' ? $this->edit_calle : null,
            'prioridad' => $this->edit_prioridad !== '' ? $this->edit_prioridad : null,
            'estado' => $this->edit_estado !== '' ? $this->edit_estado : null,
            'estatus' => $this->edit_estatus !== '' ? $this->edit_estatus : null,
            'comentarios' => $this->edit_comentarios !== '' ? $this->edit_comentarios : null,
            'comentarios_extra' => $this->edit_comentarios_extra !== '' ? $this->edit_comentarios_extra : null,
            'doc_adjunto' => $this->edit_doc_adjunto !== '' ? $this->edit_doc_adjunto : null,
            'lo_resolvio' => $this->edit_lo_resolvio !== '' ? $this->edit_lo_resolvio : null,
        ]);

        // recalcula SLA si cambio la prioridad
        $t->calcularSlaVencimiento();
        $t->save();

        TicketHistorial::create([
            'ticket_id' => $t->id,
            'user_id' => Auth::id(),
            'accion' => 'editado',
            'detalles' => ['antes' => $antes, 'despues' => $t->only(array_keys($antes))],
        ]);

        $this->mostrarEditar = false;
        $this->exito = '✏️ Datos del ticket actualizados.';
    }

    // ======================================================================
    //  WORKFLOW
    // ======================================================================

    public function resolver(): void
    {
        $this->errorAccion = null;

        try {
            $this->flujo()->resolver(
                $this->ticket(),
                Auth::user(),
                $this->comentarioResolucion !== '' ? $this->comentarioResolucion : null
            );

            $this->exito = '✅ Ticket resuelto.'
                .($this->ticket()->drive_folder_url ? ' Carpeta de Drive creada.' : '');
            $this->mostrarResolver = false;
            $this->comentarioResolucion = '';
        } catch (\Throwable $e) {
            $this->errorAccion = $e->getMessage();
        }
    }

    public function cerrar(): void
    {
        $this->errorAccion = null;

        try {
            $this->flujo()->cerrar(
                $this->ticket(),
                Auth::user(),
                $this->comentarioCierre !== '' ? $this->comentarioCierre : null
            );

            $this->exito = '🔒 Ticket cerrado.';
            $this->mostrarCerrar = false;
            $this->comentarioCierre = '';
        } catch (\Throwable $e) {
            $this->errorAccion = $e->getMessage();
        }
    }

    public function reabrir(): void
    {
        $this->errorAccion = null;

        try {
            $this->flujo()->reabrir(
                $this->ticket(),
                Auth::user(),
                $this->convertirACorrectivo,
                $this->motivoReapertura !== '' ? $this->motivoReapertura : null
            );

            $this->exito = '🔄 Ticket reabierto'
                .($this->convertirACorrectivo ? ' y convertido a correctivo.' : '.');
            $this->mostrarReabrir = false;
            $this->motivoReapertura = '';
            $this->convertirACorrectivo = false;
        } catch (\Throwable $e) {
            $this->errorAccion = $e->getMessage();
        }
    }

    public function guardarAsignacion(): void
    {
        $this->errorAccion = null;

        try {
            $this->flujo()->asignar($this->ticket(), $this->tecnicosSeleccionados, Auth::user());
            $this->exito = '👷 Técnicos asignados.';
            $this->mostrarAsignar = false;
        } catch (\Throwable $e) {
            $this->errorAccion = $e->getMessage();
        }
    }

    // ======================================================================
    //  COMENTARIOS / FOTOS
    // ======================================================================

    public function agregarComentario(): void
    {
        $texto = trim($this->nuevoComentario);
        if ($texto === '') {
            return;
        }

        TicketComentario::create([
            'ticket_id' => $this->ticketId,
            'user_id' => Auth::id(),
            'tipo' => 'general',
            'texto' => $texto,
        ]);

        $this->nuevoComentario = '';
        $this->exito = '💬 Comentario agregado.';
    }

    public function subirFoto(?double $lat = null, ?double $lng = null): void
    {
        $this->errorAccion = null;

        try {
            $this->validate([
                'foto' => ['required', 'image', 'max:10240'],
                'tipoFoto' => ['required', 'in:antes,despues'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->errorAccion = 'Elegí una foto válida (jpg/png, hasta 10 MB).';

            return;
        }

        $ticket = $this->ticket();

        $ruta = $this->foto->store('fotos-tickets/'.$ticket->id, 'public');

        // copia a Google Drive (si esta configurado)
        $urlDrive = app(GoogleDriveService::class)->subirFoto(
            $ticket,
            storage_path('app/'.$ruta),
            $this->foto->getClientOriginalName(),
            $this->tipoFoto
        );

        TicketFoto::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'tipo' => $this->tipoFoto,
            'ruta' => $ruta,
            'drive_url' => $urlDrive,
            'lat' => $lat,
            'lng' => $lng,
            'tomada_at' => now(),
        ]);

        TicketHistorial::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'accion' => 'foto',
            'detalles' => ['tipo' => $this->tipoFoto, 'drive' => $urlDrive],
        ]);

        $this->foto = null;
        $this->exito = '📷 Foto subida'.($urlDrive ? ' (copiada a Drive).' : '.');
    }

    // ======================================================================
    //  RENDER
    // ======================================================================

    public function render()
    {
        $ticket = $this->ticket();
        $usuario = Auth::user();

        $tecnicosDisponibles = User::where('role', User::ROLE_TECNICO)
            ->where('activo', true)
            ->orderBy('area')
            ->orderBy('name')
            ->get();

        return view('livewire.detalle-ticket', [
            'ticket' => $ticket,
            'usuario' => $usuario,
            'tecnicosDisponibles' => $tecnicosDisponibles,
            'puedeAsignar' => $usuario->puedeGestionar(),
            'puedeResolver' => $usuario->puedeGestionar() && $ticket->puedeResolverse(),
            'puedeCerrar' => $usuario->isAdmin() && $ticket->puedeCerrarse(),
            'puedeReabrir' => $usuario->isAdmin() && $ticket->puedeReabrirse(),
        ]);
    }

    // ======================================================================
    //  INTERNOS
    // ======================================================================

    private function ticket(): Ticket
    {
        return Ticket::with(['sitio', 'tecnicos', 'comentariosSistema.user', 'fotos', 'historial.user'])
            ->findOrFail($this->ticketId);
    }

    private function flujo(): FlujoTicketService
    {
        return app(FlujoTicketService::class);
    }
}
