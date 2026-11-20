<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('zonas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);          // ej. Zona Norte, Zona Centro, Formosa Capital
            $table->string('codigo', 20)->unique(); // ej. ZN, ZC, FC
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // cada sitio pertenece a una zona
        Schema::table('sitios', function (Blueprint $table) {
            $table->foreignId('zona_id')->nullable()->constrained('zonas')->nullOnDelete()->after('provincia_nombre');
        });
    }

    public function down(): void
    {
        Schema::table('sitios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('zona_id');
        });

        Schema::dropIfExists('zonas');
    }
};
