<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketHistorial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Registro manual de tickets uno por uno (solo supervisores). */
class TicketCrearController extends Controller
{
    public function create()
    {
        $usuario = Auth::user();

        if (! $usuario->isSupervisor()) {
            abort(403, 'Solo los supervisores pueden crear tickets.');
        }

        return view('tickets.crear', [
            'prioridades' => array_keys(Ticket::PRIORIDAD_HORAS),
            'tecnicos' => $usuario->tecnicos(),
        ]);
    }

    public function store(Request $request)
    {
        $usuario = Auth::user();

        $datos = $request->validate([
            'mda' => ['nullable', 'string', 'max:100'],
            'numero_orden' => ['required', 'string', 'max:100', 'unique:tickets,numero_orden'],
            'clase_orden' => ['required', 'string', 'max:20', 'in:FSMA,FSMC'],
            'fecha_entrada' => ['required', 'date'],
            'fecha_modificacion_maestro' => ['nullable', 'date'],
            'grupo_planificador' => ['nullable', 'string', 'max:100'],
            'texto_breve' => ['required', 'string', 'max:255'],
            'descripcion' => ['nullable', 'string'],
            'calle' => ['nullable', 'string', 'max:255'],
            'ubicacion' => ['required', 'string', 'max:255'],
            'sitio_id' => ['nullable', 'exists:sitios,id'],
            'provincia_codigo' => ['required', 'string', 'max:10', 'in:A32,A33,A34,A35,A36,A41,A43,A45,A47,A48'],
            'provincia_nombre' => ['required', 'string', 'max:100'],
            'anio' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'area_empresa' => ['nullable', 'string', 'max:100'],
            'doc_adjunto' => ['nullable', 'string', 'max:255'],
            'inicio_extrema' => ['nullable', 'date'],
            'fin_extrema' => ['nullable', 'date'],
            'estatus' => ['nullable', 'string', 'max:100'],
            'prioridad' => ['required', 'string', 'max:50', Rule::in(array_keys(Ticket::PRIORIDAD_HORAS))],
            'estado' => ['nullable', 'string', 'max:100'],
            'lo_resolvio' => ['nullable', 'string', 'max:255'],
            'fecha_resolucion' => ['nullable', 'date'],
            'documentacion' => ['nullable', 'string', 'max:500'],
            'comentarios' => ['nullable', 'string'],
            'fotos_texto' => ['nullable', 'string'],
            'comentarios_extra' => ['nullable', 'string'],
        ], [
            'numero_orden.unique' => 'Ya existe un ticket con este N° de orden.',
            'numero_orden.required' => 'El N° de orden es obligatorio.',
            'ubicacion.required' => 'La ubicación es obligatoria.',
            'prioridad.required' => 'La prioridad es obligatoria.',
        ]);

        $provinciaNombres = [
            'A32' => 'CHACO', 'A33' => 'CORRIENTES', 'A34' => 'ENTRE RIOS', 'A35' => 'MISIONES',
            'A36' => 'FORMOSA', 'A41' => 'CATAMARCA', 'A43' => 'JUJUY', 'A45' => 'RIOJA',
            'A47' => 'SALTA', 'A48' => 'SANTIAGO DEL ESTERO',
        ];
        if (! $datos['provincia_nombre']) {
            $datos['provincia_nombre'] = $provinciaNombres[$datos['provincia_codigo']] ?? strtoupper($datos['provincia_codigo']);
        }

        DB::transaction(function () use ($datos, $usuario) {
            $tipo = $datos['clase_orden'] === 'FSMC' ? 'preventivo' : 'correctivo';
            // Conservamos 'clase_orden': la columna es NOT NULL en la BD.
            // El modelo no sobrescribe 'tipo' porque ya lo seteamos arriba.

            $estadoExcel = strtoupper(trim((string) ($datos['estado'] ?? '')));
            if ($estadoExcel === 'REALIZADO') {
                $datos['estado_workflow'] = Ticket::RESUELTO;
                $datos['resuelto_at'] = $datos['fecha_resolucion']
                    ? \Carbon\Carbon::parse($datos['fecha_resolucion'])->toDateTimeString()
                    : now();
            } else {
                $datos['estado_workflow'] = Ticket::NUEVO;
            }

            $datos['tipo'] = $tipo;
            $datos['importado_por'] = $usuario->id;
            $datos['importado_at'] = now();
            $datos['origen_hoja'] = 'FORMULARIO';
            $datos['fila_origen'] = null;

            $ticket = Ticket::create($datos);

            TicketHistorial::create([
                'ticket_id' => $ticket->id,
                'user_id' => $usuario->id,
                'accion' => 'creado_manual',
                'detalles' => ['numero_orden' => $ticket->numero_orden],
            ]);
        });

        $ticketCreado = Ticket::where('numero_orden', $datos['numero_orden'])->first();

        return redirect()->route('tickets.show', $ticketCreado?->id ?? $datos['numero_orden'])
            ->with('exito', 'Ticket creado correctamente.');
    }
}
