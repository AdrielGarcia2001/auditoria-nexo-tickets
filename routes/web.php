<?php

use App\Http\Controllers\AlertasController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportarTicketsController;
use App\Http\Controllers\GpsController;
use App\Http\Controllers\GoogleOAuthController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\SitiosController;
use App\Http\Controllers\UsuariosController;
use App\Models\Ticket;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

// ====== sesion ======
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware(['guest', 'throttle:5,1']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ====== registro publico (queda PENDIENTE hasta que el admin lo acepte) ======
Route::get('/registro', [AuthController::class, 'showRegistro'])->name('registro')->middleware('guest');
Route::post('/registro', [AuthController::class, 'registro'])->name('registro.post')->middleware(['guest', 'throttle:5,1']);

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // ====== tickets: todos los roles ======
    Route::get('/tickets-exportar', ExportarTicketsController::class)->name('tickets.exportar');
    Route::view('/tickets', 'tickets.index')->name('tickets.index');
    Route::get('/tickets/{ticket}', fn (Ticket $ticket) => view('tickets.show', compact('ticket')))
        ->name('tickets.show');

    // ====== perfil del usuario ======
    Route::get('/perfil', [PerfilController::class, 'index'])->name('perfil.index');
    Route::post('/perfil', [PerfilController::class, 'actualizar'])->name('perfil.actualizar');
    Route::post('/perfil/google/desconectar', [PerfilController::class, 'desconectarGoogle'])->name('perfil.google.desconectar');
    Route::post('/perfil/google/token', [PerfilController::class, 'guardarTokenGoogle'])->name('perfil.google.token');

    // ====== Google Drive OAuth (admin/supervisor) ======
    Route::get('/google/auth', [GoogleOAuthController::class, 'redirect'])->name('google.auth');
    Route::get('/google/callback', [GoogleOAuthController::class, 'callback'])->name('google.callback');

    // ====== mapa en vivo ======
    Route::view('/mapa', 'mapa.index')->name('mapa.index');

    // ====== GPS de la cuadrilla ======
    Route::post('/gps/check-in', [GpsController::class, 'checkIn'])->name('gps.checkin')->middleware('throttle:30,1');
    Route::post('/gps/check-out', [GpsController::class, 'checkOut'])->name('gps.checkout')->middleware('throttle:30,1');
    Route::post('/gps/tracking', [GpsController::class, 'tracking'])->name('gps.tracking')->middleware('throttle:60,1');
    Route::get('/gps/posiciones', [GpsController::class, 'posiciones'])->name('gps.posiciones');
    Route::get('/gps/recorrido/{user}', [GpsController::class, 'recorrido'])->name('gps.recorrido');
    Route::get('/gps/km/{user}', [GpsController::class, 'km'])->name('gps.km');

    // ====== gestion: supervisor y admin ======
    Route::middleware('role:admin,supervisor')->group(function () {
        Route::view('/importar', 'importar.index')->name('importar.index');
        Route::get('/sitios', [SitiosController::class, 'index'])->name('sitios.index');
        Route::get('/sitios/{sitio}', [SitiosController::class, 'show'])->name('sitios.show');
        Route::post('/sitios/{sitio}/tecnicos', [SitiosController::class, 'asignarTecnicos'])->name('sitios.tecnicos');
        Route::post('/sitios/{sitio}/contrato', [SitiosController::class, 'guardarContrato'])->name('sitios.contrato');
    });

    // ====== solo admin ======
    Route::middleware('role:admin')->group(function () {
        Route::get('/usuarios', [UsuariosController::class, 'index'])->name('usuarios.index');
        Route::post('/usuarios/{user}/aceptar', [UsuariosController::class, 'aceptar'])->name('usuarios.aceptar');
        Route::post('/usuarios/{user}/rechazar', [UsuariosController::class, 'rechazar'])->name('usuarios.rechazar');
        Route::post('/usuarios/{user}/alternar-activo', [UsuariosController::class, 'alternarActivo'])->name('usuarios.alternar');

        Route::get('/alertas', [AlertasController::class, 'index'])->name('alertas.index');
        Route::post('/alertas/{alerta}/leer', [AlertasController::class, 'marcarLeida'])->name('alertas.leer');
        Route::post('/alertas/leer-todas', [AlertasController::class, 'marcarTodasLeidas'])->name('alertas.leer-todas');
    });
});
