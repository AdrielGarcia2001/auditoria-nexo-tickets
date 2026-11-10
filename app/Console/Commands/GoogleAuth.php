<?php

namespace App\Console\Commands;

use Google\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Obtiene el refresh_token de Google Drive (OAuth 2.0) para que el sistema
 * pueda subir fotos y crear carpetas por ticket.
 *
 * Uso: php artisan google:auth
 */
class GoogleAuth extends Command
{
    protected $signature = 'google:auth';

    protected $description = 'Obtiene y guarda el refresh_token de Google Drive (OAuth 2.0).';

    public function handle(): int
    {
        $clientId = config('google.client_id');
        $clientSecret = config('google.client_secret');

        if (! $clientId || ! $clientSecret) {
            $this->error('Faltan GOOGLE_CLIENT_ID y GOOGLE_CLIENT_SECRET en .env');

            return self::FAILURE;
        }

        $client = new Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->setScopes(['https://www.googleapis.com/auth/drive.file']);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setRedirectUri('http://localhost');

        $url = $client->createAuthUrl();

        $this->info('Abrí esta URL en el navegador y autorizá la app:');
        $this->line($url);
        $this->newLine();
        $this->comment('Después de autorizar, el navegador te redirige a http://localhost/?code=XXXX');
        $this->comment('Copiá el valor del parámetro "code" y pegalo acá.');

        $code = $this->ask('Código de autorización');

        if (! $code) {
            $this->error('No se ingresó ningún código.');

            return self::FAILURE;
        }

        try {
            $token = $client->fetchAccessTokenWithAuthCode(trim($code));
        } catch (\Throwable $e) {
            $this->error('Error al canjear el código: '.$e->getMessage());

            return self::FAILURE;
        }

        if (isset($token['error'])) {
            $this->error('Error de Google: '.($token['error_description'] ?? $token['error']));

            return self::FAILURE;
        }

        $refreshToken = $token['refresh_token'] ?? null;

        if (! $refreshToken) {
            $this->warn('Google no devolvió refresh_token. Revocá el acceso de la app en tu cuenta y volvé a intentar (prompt=consent).');

            return self::FAILURE;
        }

        // guarda en .env
        $envPath = base_path('.env');
        $contenido = File::get($envPath);

        if (str_contains($contenido, 'GOOGLE_REFRESH_TOKEN=')) {
            $contenido = preg_replace('/^GOOGLE_REFRESH_TOKEN=.*/m', 'GOOGLE_REFRESH_TOKEN='.$refreshToken, $contenido);
        } else {
            $contenido .= "\nGOOGLE_REFRESH_TOKEN=".$refreshToken."\n";
        }

        File::put($envPath, $contenido);

        $this->info('✅ refresh_token guardado en .env');
        $this->line('GOOGLE_REFRESH_TOKEN='.$refreshToken);
        $this->newLine();
        $this->comment('Ejecutá `php artisan config:clear` para aplicar los cambios.');

        return self::SUCCESS;
    }
}
