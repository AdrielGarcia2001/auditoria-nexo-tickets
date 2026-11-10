<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PerfilController extends Controller
{
    public function index()
    {
        return view('perfil.index', ['usuario' => Auth::user()]);
    }

    public function actualizar(Request $request)
    {
        $usuario = Auth::user();

        $datos = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'telefono' => ['nullable', 'string', 'max:40'],
            'password_actual' => ['nullable', 'string'],
            'password' => ['nullable', 'confirmed', 'min:8'],
        ]);

        if (! empty($datos['password'])) {
            if (empty($datos['password_actual']) || ! Hash::check($datos['password_actual'], $usuario->password)) {
                return back()->withErrors(['password_actual' => 'La contraseña actual no es correcta.'])->withInput();
            }
            $usuario->password = $datos['password'];
        }

        $usuario->name = $datos['name'];
        $usuario->email = $datos['email'];
        $usuario->telefono = $datos['telefono'] ?? null;
        $usuario->save();

        return back()->with('exito', 'Perfil actualizado correctamente.');
    }

    public function desconectarGoogle(Request $request)
    {
        $usuario = Auth::user();
        $usuario->google_refresh_token = null;
        $usuario->save();

        return back()->with('exito', 'Google Drive desconectado.');
    }

    /** Guarda el refresh_token pegado manualmente (alternativa al OAuth). */
    public function guardarTokenGoogle(Request $request)
    {
        $usuario = Auth::user();

        if (! $usuario || ! $usuario->puedeGestionar()) {
            abort(403);
        }

        $datos = $request->validate([
            'google_refresh_token' => ['required', 'string', 'min:20'],
        ], [
            'google_refresh_token.required' => 'Pegá el refresh_token de Google.',
            'google_refresh_token.min' => 'El token parece demasiado corto.',
        ]);

        $usuario->google_refresh_token = trim($datos['google_refresh_token']);
        $usuario->save();

        return back()->with('exito', 'Token de Google Drive guardado. Ya podés subir fotos y crear carpetas por ticket.');
    }
}
