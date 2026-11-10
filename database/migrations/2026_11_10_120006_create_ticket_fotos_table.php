<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipo', 10)->default('antes');            // antes | despues
            $table->string('ruta');                                   // archivo local (storage)
            $table->string('drive_url')->nullable();                  // copia en la carpeta del ticket
            $table->double('lat')->nullable();                        // GPS al tomar la foto
            $table->double('lng')->nullable();
            $table->timestamp('tomada_at')->nullable();
            $table->timestamps();

            $table->index(['ticket_id', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_fotos');
    }
};
