<?php

namespace App\Http\Controllers;

use App\Models\ContratoSla;
use App\Models\Sitio;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;

class SitiosController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = $request->string('busqueda')->toString();

        $sitios = Sitio::query()
            ->withCount(['tickets', 'tecnicos'])
            ->with('contratoSla')
            ->when($busqueda !== '', fn ($q) => $q->where('nombre', 'like', '%'.$busqueda.'%')
                ->orWhere('calle', 'like', '%'.$busqueda.'%'))
            ->orderBy('nombre')
            ->paginate(25);

        return view('sitios.index', compact('sitios', 'busqueda'));
    }

    public function show(Sitio $sitio)
    {
        $sitio->load(['tecnicos', 'contratoSla', 'tickets' => fn ($q) => $q->latest('fecha_entrada')->limit(50)]);

        $tecnicosDisponibles = User::where('role', User::ROLE_TECNICO)
            ->where('activo', true)
            ->orderBy('area')
            ->orderBy('name')
            ->get();

        return view('sitios.show', compact('sitio', 'tecnicosDisponibles'));
    }

    /** Asigna/desasigna tecnicos fijos a un sitio. */
    public function asignarTecnicos(Request $request, Sitio $sitio)
    {
        $datos = $request->validate([
            'tecnicos' => ['array'],
            'tecnicos.*' => ['integer', 'exists:users,id'],
        ]);

        $ids = array_values(array_unique(array_map('intval', $datos['tecnicos'] ?? [])));

        // sync en tecnicos_sitios
        $sitio->tecnicos()->sync(
            collect($ids)->mapWithKeys(fn ($id) => [$id => ['asignado_por' => $request->user()->id]])->all()
        );

        return back()->with('exito', 'Técnicos fijos del sitio actualizados.');
    }

    /** Crea o actualiza el contrato SLA del sitio. */
    public function guardarContrato(Request $request, Sitio $sitio)
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string'],
            'horas' => ['nullable', 'array'],
            'horas.*' => ['nullable', 'integer', 'min:1'],
            'activo' => ['boolean'],
        ]);

        $horas = [];
        foreach (($datos['horas'] ?? []) as $prioridad => $h) {
            if ($h !== null && $h !== '') {
                $horas[$prioridad] = (int) $h;
            }
        }

        $contrato = $sitio->contratoSla;
        if ($contrato) {
            $contrato->update([
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'horas_por_prioridad' => $horas ?: null,
                'activo' => (bool) ($datos['activo'] ?? true),
            ]);
        } else {
            ContratoSla::create([
                'sitio_id' => $sitio->id,
                'nombre' => $datos['nombre'],
                'descripcion' => $datos['descripcion'] ?? null,
                'horas_por_prioridad' => $horas ?: null,
                'activo' => (bool) ($datos['activo'] ?? true),
            ]);
        }

        return back()->with('exito', 'Contrato SLA guardado.');
    }
}
