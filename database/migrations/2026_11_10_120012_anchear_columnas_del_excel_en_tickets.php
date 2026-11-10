<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El Excel real trae valores mas largos de lo esperado en algunas columnas:
 *  - CLASE ORDEN a veces contiene nombres ("BARRETO MARCELO /GAVILANHUGO")
 *  - ESTATUS puede ser "ENEJ (EN EJECUCION) (EL ADMINISTRADOR DEBE CERRAR)"
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('clase_orden', 60)->change();
            $table->string('estatus', 100)->nullable()->change();
            $table->string('prioridad', 60)->nullable()->change();
            $table->string('estado', 60)->nullable()->change();
            $table->string('grupo_planificador', 20)->nullable()->change();
            $table->string('mda', 100)->nullable()->change();
            $table->string('tipo', 30)->default('correctivo')->change();
            $table->string('estado_workflow', 30)->default('nuevo')->change();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('clase_orden', 10)->change();
            $table->string('estatus', 30)->nullable()->change();
            $table->string('prioridad', 30)->nullable()->change();
            $table->string('estado', 30)->nullable()->change();
            $table->string('grupo_planificador', 10)->nullable()->change();
            $table->string('mda')->nullable()->change();
            $table->string('tipo', 20)->default('correctivo')->change();
            $table->string('estado_workflow', 20)->default('nuevo')->change();
        });
    }
};
