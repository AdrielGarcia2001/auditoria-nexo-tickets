<?php

namespace App\Http\Controllers;

use App\Models\SupervisorTecnico;
use App\Models\SupervisorZona;
use App\Models\User;
use App\Models\Zona;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Panel del administrador: creación y gestión de cuentas de usuarios.
 * - El administrador crea cuentas con contraseña aleatoria.
 * - El usuario debe cambiar su contraseña al primer login.
 */
class UsuariosController extends Controller
{
    public function index()
    {
        $usuarios = User::orderByDesc('role')->orderBy('name')->get();

        return view('usuarios.index', ['usuarios' => $usuarios]);
    }

    public function crear()
    {
        return view('usuarios.crear');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,supervisor,tecnico'],
            'area' => ['nullable', 'in:mantenimiento,limpieza'],
            'telefono' => ['nullable', 'string', 'max:40'],
        ]);

        $passwordAleatorio = Str::random(12);

        $usuario = User::create([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'password' => bcrypt($passwordAleatorio),
            'role' => $datos['role'],
            'area' => $datos['area'] ?? null,
            'telefono' => $datos['telefono'] ?? null,
            'activo' => true,
            'password_cambiado' => false,
        ]);

        return back()->with('exito', "Cuenta de {$usuario->name} creada. Contraseña temporal: <strong>{$passwordAleatorio}</strong>. El usuario deberá cambiarla al primer ingreso.");
    }

    public function alternarActivo(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No podés desactivar tu propia cuenta.');
        }

        $user->update(['activo' => ! $user->activo]);

        $estado = $user->activo ? 'activada' : 'desactivada';

        return back()->with("exito", "Cuenta de {$user->name} {$estado}.");
    }

    // ====== asignar zonas a supervisores ======

    public function zonas(User $user)
    {
        if (! $user->isSupervisor()) {
            return back()->with('error', 'Solo se pueden asignar zonas a supervisores.');
        }

        return view('usuarios.zonas', [
            'supervisor' => $user,
            'zonas' => Zona::orderBy('nombre')->get(),
            'zonasAsignadas' => $user->zonaIds(),
            'tecnicosAsignados' => $user->tecnicoIds(),
            'todosTecnicos' => User::where('role', User::ROLE_TECNICO)->where('activo', true)->orderBy('name')->get(),
        ]);
    }

    public function zonasStore(Request $request, User $user)
    {
        if (! $user->isSupervisor()) {
            return back()->with('error', 'Solo se pueden asignar zonas a supervisores.');
        }

        $datos = $request->validate([
            'zonas' => ['array'],
            'zonas.*' => ['integer', 'exists:zonas,id'],
            'tecnicos' => ['array'],
            'tecnicos.*' => ['integer', 'exists:users,id'],
        ]);

        $zonaIds = array_values(array_unique(array_map('intval', $datos['zonas'] ?? [])));
        $tecnicoIds = array_values(array_unique(array_map('intval', $datos['tecnicos'] ?? [])));

        $user->zonasAsignadas()->delete();
        foreach ($zonaIds as $zonaId) {
            SupervisorZona::create([
                'supervisor_id' => $user->id, 'zona_id' => $zonaId, 'asignado_en' => now(),
            ]);
        }

        $user->tecnicosAsignados()->delete();
        foreach ($tecnicoIds as $tecnicoId) {
            SupervisorTecnico::create([
                'supervisor_id' => $user->id, 'tecnico_id' => $tecnicoId, 'asignado_en' => now(),
            ]);
        }

        return back()->with('exito', "Zonas y técnicos de {$user->name} actualizados.");
    }
}
