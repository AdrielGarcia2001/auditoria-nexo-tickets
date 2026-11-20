<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervisor_zonas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervisor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('zona_id')->constrained('zonas')->cascadeOnDelete();
            $table->timestamp('asignado_en')->useCurrent();
            $table->timestamps();

            $table->unique(['supervisor_id', 'zona_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisor_zonas');
    }
};
