<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas_sla', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->string('tipo', 20);                 // por_vencer | vencido
            $table->text('mensaje');
            $table->timestamp('leida_at')->nullable();
            $table->timestamps();

            $table->unique(['ticket_id', 'tipo']);
            $table->index('leida_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas_sla');
    }
};
