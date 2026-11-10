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
            throw ValidationException::withMessages([
                'email' => 'Credenciales inválidas.',
            ]);
        }

        // la solicitud recien hecha espera al administrador
        if ($usuario->solicitudPendiente()) {
            throw ValidationException::withMessages([
                'email' => 'Tu cuenta está pendiente de aprobación del administrador. Todavía no podés ingresar.',
            ]);
        }

        if ($usuario->solicitudRechazada()) {
            throw ValidationException::withMessages([
                'email' => 'Tu solicitud de registro fue rechazada. Contactá al administrador del sistema.',
            ]);
        }

        if (! $usuario->activo) {
            throw ValidationException::withMessages([
                'email' => 'Tu cuenta está desactivada. Contactá al administrador del sistema.',
            ]);
        }

        Auth::login($usuario, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    // ======================================================================
    //  REGISTRO PUBLICO: el usuario elige su rol y queda PENDIENTE del admin
    // ======================================================================

    public function showRegistro()
    {
        return view('auth.registro');
    }

    public function registro(Request $request)
    {
        $datos = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'telefono' => ['nullable', 'string', 'max:40'],
            // el usuario elige su perfil (nunca puede registrarse como admin)
            'perfil' => ['required', 'in:supervisor,tecnico-mantenimiento,tecnico-limpieza'],
        ], [
            'perfil.required' => 'Elegí el perfil con el que te vas a registrar.',
            'perfil.in' => 'Elegí un perfil válido.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'email.unique' => 'Ya existe una cuenta con ese email.',
        ]);

        [$role, $area] = match ($datos['perfil']) {
            'supervisor' => [User::ROLE_SUPERVISOR, null],
            'tecnico-mantenimiento' => [User::ROLE_TECNICO, User::AREA_MANTENIMIENTO],
            default => [User::ROLE_TECNICO, User::AREA_LIMPIEZA],
        };

        User::create([
            'name' => $datos['name'],
            'email' => $datos['email'],
            'password' => $datos['password'],
            'role' => $role,
            'area' => $area,
            'telefono' => $datos['telefono'] ?? null,
            'activo' => false,
            'solicitud_estado' => User::SOLICITUD_PENDIENTE,
            'solicitud_at' => now(),
        ]);

        return redirect()->route('login')
            ->with('exito', 'Solicitud de registro enviada. Tu cuenta será activada cuando el administrador la acepte.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
