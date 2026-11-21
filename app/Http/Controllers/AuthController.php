<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $usuario = User::where('email', $credenciales['email'])->first();

        if (! $usuario || ! Hash::check($credenciales['password'], $usuario->password)) {
            throw ValidationException::withMessages(['email' => 'Credenciales inválidas.']);
        }

        if (! $usuario->activo) {
            throw ValidationException::withMessages([
                'email' => 'Tu cuenta está desactivada. Contactá al administrador del sistema.',
            ]);
        }

        Auth::login($usuario, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($usuario->necesitaCambiarPassword()) {
            return redirect()->route('password.cambiar')
                ->with('aviso', 'Por seguridad, debés cambiar tu contraseña antes de continuar.');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
