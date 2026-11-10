<?php

namespace App\Services\Google;

use App\Models\Ticket;
use Google\Client;
use Google\Service\Drive as DriveService;
use Google\Service\Drive\DriveFile;

/**
 * Carpetas y fotos en Google Drive.
 *
 * Requiere en .env: GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET,
 * GOOGLE_REFRESH_TOKEN (y opcional GOOGLE_PARENT_FOLDER_ID).
 * Sin configurar, el sistema funciona igual (drive_folder_url queda vacio).
 */
class GoogleDriveService
{
    private ?Client $cliente = null;

    /** Refresh token efectivo: primero el del usuario autenticado (admin/supervisor), luego .env. */
    public function refreshTokenEfectivo(): ?string
    {
        try {
            $user = auth()->user();
            if ($user && filled($user->google_refresh_token)) {
                return $user->google_refresh_token;
            }
        } catch (\Throwable $e) {
            // sin contexto de request (CLI): usar .env
        }

        return config('google.refresh_token');
    }

    public function estaConfigurado(): bool
    {
        return filled(config('google.client_id'))
            && filled(config('google.client_secret'))
            && filled($this->refreshTokenEfectivo());
    }

    /**
     * Crea la carpeta del ticket (N° de orden) con descripcion/comentario.
     * Devuelve la URL o null si Drive no esta configurado o falla.
     */
    public function crearCarpetaTicket(Ticket $ticket, ?string $descripcion = null): ?string
    {
        if (! $this->estaConfigurado()) {
            return null;
        }

        try {
            $drive = new DriveService($this->cliente());

            $nombre = 'Ticket '.$ticket->numero_orden;
            if ($ticket->sitio) {
                $nombre .= ' - '.$ticket->sitio->nombre;
            } elseif ($ticket->ubicacion) {
                $nombre .= ' - '.$ticket->ubicacion;
            }

            $texto = $descripcion
                ?? trim(($ticket->texto_breve ? 'Asunto: '.$ticket->texto_breve.PHP_EOL : '')
                    .($ticket->comentarios ?: ''));

            $metadata = new DriveFile([
                'name' => $nombre,
                'mimeType' => 'application/vnd.google-apps.folder',
                'description' => $texto !== '' ? $texto : null,
            ]);

            $padre = config('google.parent_folder_id');
            if ($padre) {
                $metadata->setParents([$padre]);
            }

            $carpeta = $drive->files->create($metadata, ['fields' => 'id']);

            return 'https://drive.google.com/drive/folders/'.$carpeta->getId();
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /**
     * Sube una foto (antes/despues) a la carpeta del ticket.
     * Si el ticket no tiene carpeta, la crea primero.
     */
    public function subirFoto(Ticket $ticket, string $rutaLocal, string $nombreArchivo, string $tipo = 'antes'): ?string
    {
        if (! $this->estaConfigurado() || ! is_file($rutaLocal)) {
            return null;
        }

        try {
            $drive = new DriveService($this->cliente());

            $carpetaId = $this->idCarpeta($ticket);
            if (! $carpetaId) {
                $url = $this->crearCarpetaTicket($ticket);
                if ($url) {
                    $ticket->drive_folder_url = $url;
                    $ticket->saveQuietly();
                    $carpetaId = $this->idCarpeta($ticket);
                }

                if (! $carpetaId) {
                    return null;
                }
            }

            $archivo = new DriveFile([
                'name' => $tipo.'_'.$nombreArchivo,
                'parents' => [$carpetaId],
            ]);

            $subido = $drive->files->create($archivo, [
                'data' => $rutaLocal,
                'mimeType' => mime_content_type($rutaLocal) ?: 'image/jpeg',
                'uploadType' => 'multipart',
                'fields' => 'id, webViewLink',
            ]);

            return $subido->getWebViewLink()
                ?: 'https://drive.google.com/file/d/'.$subido->getId().'/view';
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /** Extrae el ID de la carpeta del ticket desde su URL de Drive. */
    private function idCarpeta(Ticket $ticket): ?string
    {
        $url = $ticket->drive_folder_url;
        if (! $url) {
            return null;
        }

        if (preg_match('#/folders/([a-zA-Z0-9_-]+)#', $url, $m)) {
            return $m[1];
        }

        return null;
    }

    private function cliente(): Client
    {
        if ($this->cliente) {
            return $this->cliente;
        }

        $cliente = new Client();
        $cliente->setClientId(config('google.client_id'));
        $cliente->setClientSecret(config('google.client_secret'));
        $cliente->setRefreshToken($this->refreshTokenEfectivo());
        $cliente->setScopes([DriveService::DRIVE_FILE]);

        return $this->cliente = $cliente;
    }
}
