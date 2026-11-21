<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('importaciones_excel', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_archivo');
            $table->string('hash_archivo')->unique(); // para detectar re-subidas
            $table->foreignId('usuario_id')->constrained('users')->cascadeOnDelete();
            $table->integer('total_filas')->default(0);
            $table->integer('importados')->default(0);
            $table->timestamp('importado_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('importaciones_excel');
    }
};
