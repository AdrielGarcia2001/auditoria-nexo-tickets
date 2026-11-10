<?php

namespace App\Livewire;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Listado de tickets con filtros (estado, prioridad, busqueda, tecnico).
 * - Admin/supervisor: ven todos.
 * - Tecnico: solo sus tickets asignados.
 */
class ListaTickets extends Component
{
    use WithPagination;

    public string $busqueda = '';
    public string $filtroEstado = '';      // nuevo | resuelto | cerrado
    public string $filtroPrioridad = '';
    public string $filtroArea = '';        // limpieza | mantenimiento
    public string $filtroSla = '';         // '' | por_vencer | vencido
    public string $orden = 'fecha_entrada'; // fecha_entrada | sla_vencimiento | prioridad

    protected $queryString = [
        'busqueda' => ['except' => ''],
        'filtroEstado' => ['except' => ''],
        'filtroPrioridad' => ['except' => ''],
        'filtroArea' => ['except' => ''],
        'filtroSla' => ['except' => ''],
        'orden' => ['except' => 'fecha_entrada'],
    ];

    public function updatingBusqueda(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroSla(): void
    {
        $this->resetPage();
    }

    public function actualizar(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $usuario = Auth::user();

        $query = Ticket::query()
            ->with(['sitio', 'tecnicos'])
            ->when($usuario->isTecnico(), fn ($q) => $q->delTecnico($usuario))
            ->when($this->busqueda !== '', function ($q) {
                $termino = '%'.$this->busqueda.'%';
                $q->where(function ($q2) use ($termino) {
                    $q2->where('numero_orden', 'like', $termino)
                        ->orWhere('texto_breve', 'like', $termino)
                        ->orWhere('ubicacion', 'like', $termino)
                        ->orWhere('calle', 'like', $termino)
                        ->orWhere('lo_resolvio', 'like', $termino);
                });
            })
            ->when($this->filtroEstado !== '', fn ($q) => $q->where('estado_workflow', $this->filtroEstado))
            ->when($this->filtroPrioridad !== '', fn ($q) => $q->where('prioridad', $this->filtroPrioridad))
            ->when($this->filtroArea !== '', fn ($q) => $q->whereRaw(
                "CASE WHEN origen_hoja = 'LIMP (M01PR012)' OR grupo_planificador = 'M02' THEN 'limpieza' ELSE 'mantenimiento' END = ?",
                [$this->filtroArea]
            ))
            ->when($this->filtroSla === 'por_vencer', fn ($q) => $q->whereNotNull('sla_vencimiento')
                ->whereBetween('sla_vencimiento', [now(), now()->addDay()])
                ->where('estado_workflow', '!=', Ticket::CERRADO))
            ->when($this->filtroSla === 'vencido', fn ($q) => $q->whereNotNull('sla_vencimiento')
                ->where('sla_vencimiento', '<', now())
                ->where('estado_workflow', '!=', Ticket::CERRADO));

        match ($this->orden) {
            'sla_vencimiento' => $query->orderByDesc('sla_vencimiento'),
            'prioridad' => $query->orderBy('prioridad'),
            default => $query->orderByDesc('fecha_entrada'),
        };

        return view('livewire.lista-tickets', [
            'tickets' => $query->paginate(20),
            'prioridades' => array_keys(Ticket::PRIORIDAD_HORAS) + ['Programado Mensual'],
            'totales' => [
                'nuevos' => Ticket::where('estado_workflow', Ticket::NUEVO)
                    ->when($usuario->isTecnico(), fn ($q) => $q->delTecnico($usuario))->count(),
                'resueltos' => Ticket::where('estado_workflow', Ticket::RESUELTO)
                    ->when($usuario->isTecnico(), fn ($q) => $q->delTecnico($usuario))->count(),
                'cerrados' => Ticket::where('estado_workflow', Ticket::CERRADO)
                    ->when($usuario->isTecnico(), fn ($q) => $q->delTecnico($usuario))->count(),
            ],
        ]);
    }
}
