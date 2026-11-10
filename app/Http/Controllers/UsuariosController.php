<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * Panel del administrador: solicitudes de registro y cuentas de usuarios.
 *
 * - Aceptar: la solicitud pasa a "aceptado" y el usuario ya puede entrar.
 * - Rechazar: queda "rechazado" y no puede entrar.
 * - Alternar activo: activa/desactiva una cuenta ya existente.
 */
class UsuariosController extends Controller
{
    public function index()
    {
        $pendientes = User::where('solicitud_estado', User::SOLICITUD_PENDIENTE)
            ->orderBy('solicitud_at')
            ->get();

        $usuarios = User::where('solicitud_estado', '!=', User::SOLICITUD_PENDIENTE)
            ->orderByDesc('role')
            ->orderBy('name')
            ->get();

        return view('usuarios.index', [
            'pendientes' => $pendientes,
            'usuarios' => $usuarios,
        ]);
    }

    public function aceptar(User $user)
    {
        if (! $user->solicitudPendiente()) {
            return back()->with('error', 'Esa solicitud ya fue procesada.');
        }

        $user->update([
            'solicitud_estado' => User::SOLICITUD_ACEPTADO,
            'aprobado_at' => now(),
            'aprobado_por' => auth()->id(),
            'activo' => true,
        ]);

        return back()->with('exito', "Cuenta de {$user->name} aceptada. Ya puede ingresar al sistema.");
    }

    public function rechazar(User $user)
    {
        if (! $user->solicitudPendiente()) {
            return back()->with('error', 'Esa solicitud ya fue procesada.');
        }

        $user->update([
            'solicitud_estado' => User::SOLICITUD_RECHAZADO,
            'aprobado_at' => now(),
            'aprobado_por' => auth()->id(),
            'activo' => false,
        ]);

        return back()->with('exito', "Solicitud de {$user->name} rechazada. No podrá ingresar.");
    }

    public function alternarActivo(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No podés desactivar tu propia cuenta.');
        }

        if ($user->solicitudPendiente()) {
            return back()->with('error', 'Primero aceptá o rechazá esa solicitud.');
        }

        $user->update(['activo' => ! $user->activo]);

        $estado = $user->activo ? 'activada' : 'desactivada';

        return back()->with('exito', "Cuenta de {$user->name} {$estado}.");
    }
}
