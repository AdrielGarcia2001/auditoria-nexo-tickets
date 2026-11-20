<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_fotos', function (Blueprint $table) {
            $table->string('direccion')->nullable()->after('lng');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_fotos', function (Blueprint $table) {
            $table->dropColumn('direccion');
        });
    }
};
