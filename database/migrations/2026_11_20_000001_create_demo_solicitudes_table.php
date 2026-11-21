<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_solicitudes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('email', 150);
            $table->string('empresa', 150)->nullable();
            $table->string('telefono', 40)->nullable();
            $table->text('mensaje')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_solicitudes');
    }
};
