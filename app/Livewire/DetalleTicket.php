<?php

namespace App\Livewire;

use App\Models\ArticuloInventario;
use App\Models\ConsumibleTicket;
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

/** Ficha completa del ticket: campos del Excel + workflow + fotos + insumos. */
class DetalleTicket extends Component
{
    use WithFileUploads;

    public int $ticketId;

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

    public string $nuevoComentario = '';

    // ====== edicion de datos del ticket (admin y supervisor) ======
    public bool $mostrarEditar = false;
    public string $edit_texto_breve = '';
    public string $edit_descripcion = '';
    public string $edit_ubicacion = '';
    public string $edit_calle = '';
    public string $edit_prioridad = '';
    public string $edit_estado = '';
    public string $edit_estatus = '';
    public string $edit_comentarios = '';
    public string $edit_lo_resolvio = '';

    // ====== insumos / consumibles ======
    public $articuloId = null;
    public int $cantidad = 1;

    // ====== fotos ======
    public ?\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $foto = null;

    public ?string $exito = null;
    public ?string $errorAccion = null;

    public function mount(int $ticketId): void
    {
        $this->ticketId = $ticketId;
        $this->tecnicosSeleccionados = $this->ticket()->tecnicos()->pluck('users.id')->all();
    }

    // ======================================================================
    //  EDICION DE DATOS (admin y supervisor)
    // ======================================================================

    /** Abre el modal de edicion con los valores actuales del ticket. */
    public function abrirEditar(): void
    {
        if (! Auth::user()?->puedeGestionar()) {
            $this->errorAccion = 'Solo supervisores y administradores pueden editar el ticket.';
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
        $this->edit_lo_resolvio = (string) ($t->lo_resolvio ?? '');
        $this->mostrarEditar = true;
        $this->errorAccion = null;
    }

    /** Guarda los cambios y registra el historial. */
    public function guardarEdicion(): void
    {
        if (! Auth::user()?->puedeGestionar()) {
            $this->errorAccion = 'Solo supervisores y administradores pueden editar el ticket.';
            return;
        }

        $this->validate([
            'edit_texto_breve' => ['required', 'string', 'max:255'],
            'edit_prioridad' => ['required', 'string'],
        ]);

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
            'lo_resolvio' => $t->lo_resolvio,
        ];

        $t->fill([
            'texto_breve' => $this->edit_texto_breve,
            'descripcion' => $this->edit_descripcion !== '' ? $this->edit_descripcion : null,
            'ubicacion' => $this->edit_ubicacion !== '' ? $this->edit_ubicacion : null,
            'calle' => $this->edit_calle !== '' ? $this->edit_calle : null,
            'prioridad' => $this->edit_prioridad,
            'estado' => $this->edit_estado !== '' ? $this->edit_estado : null,
            'estatus' => $this->edit_estatus !== '' ? $this->edit_estatus : null,
            'comentarios' => $this->edit_comentarios !== '' ? $this->edit_comentarios : null,
            'lo_resolvio' => $this->edit_lo_resolvio !== '' ? $this->edit_lo_resolvio : null,
        ]);

        $t->save(); // el evento saving recalcula el SLA

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

        $usuario = Auth::user();

        // un supervisor solo puede asignar a sus propios tecnicos
        if ($usuario->isSupervisor()) {
            $tecnicosValidos = $usuario->tecnicoIds();
            $invalidos = array_diff($this->tecnicosSeleccionados, $tecnicosValidos);
            if (! empty($invalidos)) {
                $this->errorAccion = 'Solo podés asignar técnicos que estén bajo tu supervisión.';
                return;
            }
        }

        try {
            $this->flujo()->asignar($this->ticket(), $this->tecnicosSeleccionados, $usuario);
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

    public function subirFoto(?float $lat = null, ?float $lng = null): void
    {
        $this->errorAccion = null;

        // solo tecnicos pueden subir fotos
        if (! Auth::user()?->isTecnico()) {
            $this->errorAccion = 'Solo los técnicos pueden subir fotos.';
            return;
        }

        try {
            $this->validate(['foto' => ['required', 'image', 'max:10240']]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->errorAccion = 'Elegí una foto válida (jpg/png, hasta 10 MB).';
            return;
        }

        $ticket = $this->ticket();
        $rutaComprimida = $this->comprimirImagen($this->foto, 'fotos-tickets/'.$ticket->id);

        $urlDrive = app(GoogleDriveService::class)->subirFoto(
            $ticket,
            storage_path('app/'.$rutaComprimida),
            $this->foto->getClientOriginalName(),
            'foto'
        );

        TicketFoto::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'tipo' => 'foto',
            'ruta' => $rutaComprimida,
            'drive_url' => $urlDrive,
            'lat' => $lat,
            'lng' => $lng,
            'direccion' => '',
            'tomada_at' => now(),
        ]);

        TicketHistorial::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'accion' => 'foto',
            'detalles' => ['tipo' => 'foto', 'drive' => $urlDrive],
        ]);

        $this->foto = null;
        $this->exito = '📷 Foto subida'.($urlDrive ? ' (copiada a Drive).' : '.');
    }

    /** Comprime una imagen subida con Livewire y devuelve la ruta guardada. */
    private function comprimirImagen(\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file, string $carpeta): string
    {
        $rutaOrigen = $file->getRealPath();
        $info = getimagesize($rutaOrigen);
        $anchoOrigen = $info[0];
        $altoOrigen = $info[1];

        $max = 1920;
        $ratio = $anchoOrigen / $altoOrigen;
        if ($anchoOrigen > $max || $altoOrigen > $max) {
            if ($anchoOrigen > $altoOrigen) {
                $ancho = $max;
                $alto = (int) round($max / $ratio);
            } else {
                $alto = $max;
                $ancho = (int) round($max * $ratio);
            }
        } else {
            $ancho = $anchoOrigen;
            $alto = $altoOrigen;
        }

        $destino = imagecreatetruecolor($ancho, $alto);

        switch ($info['mime']) {
            case 'image/jpeg':
                $origen = imagecreatefromjpeg($rutaOrigen);
                imagecopyresampled($destino, $origen, 0, 0, 0, 0, $ancho, $alto, $anchoOrigen, $altoOrigen);
                $rutaGuardado = storage_path('app/public/'.$carpeta.'/'.uniqid().'.jpg');
                @mkdir(dirname($rutaGuardado), 0755, true);
                imagejpeg($destino, $rutaGuardado, 88);
                imagedestroy($origen);
                break;
            case 'image/png':
                $origen = imagecreatefrompng($rutaOrigen);
                imagecopyresampled($destino, $origen, 0, 0, 0, 0, $ancho, $alto, $anchoOrigen, $altoOrigen);
                $rutaGuardado = storage_path('app/public/'.$carpeta.'/'.uniqid().'.png');
                @mkdir(dirname($rutaGuardado), 0755, true);
                imagepng($destino, $rutaGuardado, 8);
                imagedestroy($origen);
                break;
            default:
                $rutaGuardado = $file->store($carpeta, 'public');
        }

        imagedestroy($destino);

        return str_replace(storage_path('app/public/').'', '', $rutaGuardado);
    }

    // ======================================================================
    //  INSUMOS / CONSUMIBLES
    // ======================================================================

    public function registrarConsumible(): void
    {
        $this->errorAccion = null;

        if (! Auth::user()?->puedeGestionar()) {
            $this->errorAccion = 'Solo supervisores y administradores pueden registrar insumos.';
            return;
        }

        $this->validate([
            'articuloId' => ['required', 'exists:articulos_inventario,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $articulo = ArticuloInventario::findOrFail($this->articuloId);

        ConsumibleTicket::create([
            'ticket_id' => $this->ticketId,
            'articulo_id' => $articulo->id,
            'cantidad_utilizada' => $this->cantidad,
            'precio_unitario_momento' => $articulo->precio_unitario,
            'registrado_por' => Auth::id(),
            'consumido_at' => now(),
        ]);

        $articulo->decrement('stock_actual', $this->cantidad);

        $this->articuloId = null;
        $this->cantidad = 1;
        $this->exito = '🧾 Insumo registrado y stock actualizado.';
    }

    // ======================================================================
    //  RENDER
    // ======================================================================

    public function render()
    {
        $ticket = $this->ticket();
        $usuario = Auth::user();

        if ($usuario->isSupervisor()) {
            $tecnicosDisponibles = $usuario->tecnicos()->get();
        } elseif ($usuario->isAdmin()) {
            $tecnicosDisponibles = User::where('role', User::ROLE_TECNICO)
                ->where('activo', true)
                ->orderBy('area')
                ->orderBy('name')
                ->get();
        } else {
            $tecnicosDisponibles = collect();
        }

        $articulos = ArticuloInventario::where('activo', true)->orderBy('nombre_articulo')->get();

        return view('livewire.detalle-ticket', [
            'ticket' => $ticket,
            'usuario' => $usuario,
            'tecnicosDisponibles' => $tecnicosDisponibles,
            'articulos' => $articulos,
            'consumibles' => $ticket->consumibles()->with('articulo')->latest('consumido_at')->get(),
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
