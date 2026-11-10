<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sitios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');                       // UBICACION / Denominacion de la ubicacion tecnica
            $table->string('calle')->nullable();            // CALLE
            $table->string('provincia_codigo', 10)->default('A36'); // A36 = FORMOSA
            $table->string('provincia_nombre', 60)->default('FORMOSA');
            $table->double('lat')->nullable();              // geocode pendiente
            $table->double('lng')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->unique(['nombre', 'provincia_codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sitios');
    }
};
