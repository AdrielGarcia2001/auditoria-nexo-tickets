<?php

namespace App\Http\Controllers;

use Google\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Flujo OAuth de Google Drive desde la web (para admin/supervisor).
 */
class GoogleOAuthController extends Controller
{
    public function redirect()
    {
        $user = Auth::user();
        if (! $user || ! $user->puedeGestionar()) {
            abort(403);
        }

        $clientId = config('google.client_id');
        $clientSecret = config('google.client_secret');

        if (! $clientId || ! $clientSecret) {
            return back()->with('error', 'Faltan GOOGLE_CLIENT_ID y GOOGLE_CLIENT_SECRET en el servidor.');
        }

        $client = new Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setScopes(['https://www.googleapis.com/auth/drive.file']);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setRedirectUri(route('google.callback'));

        session(['google_oauth_state' => bin2hex(random_bytes(16))]);
        $client->setState(session('google_oauth_state'));

        return redirect()->away($client->createAuthUrl());
    }

    public function callback(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! $user->puedeGestionar()) {
            abort(403);
        }

        if ($request->input('state') !== session('google_oauth_state')) {
            return redirect()->route('perfil.index')->with('error', 'Estado OAuth inválido. Intentá de nuevo.');
        }

        $code = $request->input('code');
        if (! $code) {
            return redirect()->route('perfil.index')->with('error', 'No se recibió código de autorización.');
        }

        $client = new Client();
        $client->setClientId(config('google.client_id'));
        $client->setClientSecret(config('google.client_secret'));
        $client->setRedirectUri(route('google.callback'));

        try {
            $token = $client->fetchAccessTokenWithAuthCode($code);
        } catch (\Throwable $e) {
            return redirect()->route('perfil.index')->with('error', 'Error al canjear el código: '.$e->getMessage());
        }

        if (isset($token['error'])) {
            return redirect()->route('perfil.index')->with('error', 'Error de Google: '.($token['error_description'] ?? $token['error']));
        }

        $refreshToken = $token['refresh_token'] ?? null;
        if (! $refreshToken) {
            return redirect()->route('perfil.index')->with('error', 'Google no devolvió refresh_token. Revocá el acceso y volvé a intentar.');
        }

        $user->google_refresh_token = $refreshToken;
        $user->save();

        return redirect()->route('perfil.index')->with('exito', 'Google Drive conectado correctamente.');
    }
}
