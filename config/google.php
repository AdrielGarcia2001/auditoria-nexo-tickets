<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Drive (fotos antes/despues y carpetas por N° de ticket)
    |--------------------------------------------------------------------------
    | Crear un proyecto en Google Cloud, habilitar Drive API, generar el
    | client_id/client_secret OAuth (Desktop app) y obtener el refresh_token
    | con el comando `php artisan google:auth`.
    */

    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'refresh_token' => env('GOOGLE_REFRESH_TOKEN'),

    // carpeta raiz donde se crean las carpetas de cada ticket (opcional)
    'parent_folder_id' => env('GOOGLE_PARENT_FOLDER_ID'),
];
