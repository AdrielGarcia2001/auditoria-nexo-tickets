<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // sitio fijo del tecnico (limpieza: 1 por sitio; mantenimiento: hasta 2)
        Schema::create('tecnicos_sitios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sitio_id')->constrained('sitios')->cascadeOnDelete();
            $table->foreignId('tecnico_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('asignado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['sitio_id', 'tecnico_id']);
            $table->index('tecnico_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tecnicos_sitios');
    }
};
