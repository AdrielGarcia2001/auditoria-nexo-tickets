<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // rol: admin | supervisor | tecnico
            $table->string('role', 20)->default('tecnico')->after('password');
            // area del tecnico: mantenimiento | limpieza (null para admin/supervisor)
            $table->string('area', 20)->nullable()->after('role');
            $table->string('telefono', 40)->nullable()->after('area');
            $table->boolean('activo')->default(true)->after('telefono');
            // ultima posicion conocida (para el mapa en tiempo real)
            $table->double('ultimo_lat')->nullable()->after('activo');
            $table->double('ultimo_lng')->nullable()->after('ultimo_lat');
            $table->timestamp('ultimo_seen_at')->nullable()->after('ultimo_lng');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role', 'area', 'telefono', 'activo',
                'ultimo_lat', 'ultimo_lng', 'ultimo_seen_at',
            ]);
        });
    }
};
