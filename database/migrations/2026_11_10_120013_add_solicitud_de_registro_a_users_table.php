<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registro de usuarios con aprobacion del administrador.
 *
 * El usuario se registra eligiendo su perfil:
 *   - supervisor
 *   - tecnico de mantenimiento
 *   - tecnico de limpieza
 *
 * Hasta que el administrador no acepta la solicitud, la cuenta no puede entrar
 * (solicitud_estado = pendiente y activo = false).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // pendiente = esperando al admin | aceptado | rechazado
            $table->string('solicitud_estado', 20)->default('aceptado')->after('activo');
            $table->timestamp('solicitud_at')->nullable()->after('solicitud_estado');
            $table->timestamp('aprobado_at')->nullable()->after('solicitud_at');
            $table->foreignId('aprobado_por')->nullable()->after('aprobado_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('aprobado_por');
            $table->dropColumn(['solicitud_estado', 'solicitud_at', 'aprobado_at']);
        });
    }
};
