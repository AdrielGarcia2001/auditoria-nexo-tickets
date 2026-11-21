<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consumibles_ticket', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('articulo_id')->constrained('articulos_inventario')->cascadeOnDelete();
            $table->unsignedInteger('cantidad_utilizada')->default(1);
            $table->decimal('precio_unitario_momento', 12, 2)->default(0); // precio al consumir
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('consumido_at')->useCurrent();
            $table->timestamps();

            $table->index(['ticket_id', 'consumido_at']);
            $table->index('consumido_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consumibles_ticket');
    }
};
