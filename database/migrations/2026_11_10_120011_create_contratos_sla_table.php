<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratos_sla', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sitio_id')->nullable()->constrained('sitios')->nullOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            // {"24 horas": 24, "48 horas": 48, "7 Días": 168, ...} horas por prioridad
            $table->jsonb('horas_por_prioridad')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos_sla');
    }
};
