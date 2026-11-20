<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Cambio obligatorio de contraseña al primer login.
 */
class PasswordController extends Controller
{
    public function showCambiar(Request $request)
    {
        $usuario = Auth::user();

        if (! $usuario || ! $usuario->necesitaCambiarPassword()) {
            return redirect()->route('dashboard');
        }

        return view('auth.cambiar-password', ['usuario' => $usuario]);
    }

    public function cambiar(Request $request)
    {
        $usuario = Auth::user();

        if (! $usuario || ! $usuario->necesitaCambiarPassword()) {
            return redirect()->route('dashboard');
        }

        $datos = $request->validate([
            'password_actual' => ['required', 'string'],
            'password' => ['required', 'confirmed', 'min:8', 'different:password_actual'],
        ], [
            'password_actual.required' => 'Ingresá tu contraseña actual.',
            'password.required' => 'Ingresá la nueva contraseña.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.different' => 'La nueva contraseña no puede ser igual a la actual.',
        ]);

        if (! Hash::check($datos['password_actual'], $usuario->password)) {
            throw ValidationException::withMessages([
                'password_actual' => 'La contraseña actual no es correcta.',
            ]);
        }

        $usuario->update([
            'password' => Hash::make($datos['password']),
            'password_cambiado' => true,
        ]);

        return redirect()->route('dashboard')
            ->with('exito', 'Contraseña cambiada correctamente. Ya podés usar el sistema.');
    }
}
