<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registros_gps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->string('tipo', 20)->default('tracking');   // check_in | check_out | tracking
            $table->double('lat');
            $table->double('lng');
            $table->double('precision_m')->nullable();
            $table->double('velocidad_kmh')->nullable();
            $table->timestamp('registrado_at')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'registrado_at']);
            $table->index('ticket_id');
        });

        // columna geografica PostGIS + indice GiST (para mapas y km recorridos)
        DB::statement('ALTER TABLE registros_gps ADD COLUMN IF NOT EXISTS punto geometry(Point, 4326)');
        DB::statement('CREATE INDEX IF NOT EXISTS idx_registros_gps_punto ON registros_gps USING GIST (punto)');

        // trigger: mantiene `punto` sincronizado con lat/lng en cada INSERT/UPDATE
        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION gps_punto_sync() RETURNS trigger AS $$
            BEGIN
                NEW.punto := ST_SetSRID(ST_MakePoint(NEW.lng, NEW.lat), 4326);
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        SQL);
        DB::statement('DROP TRIGGER IF EXISTS trg_gps_punto ON registros_gps');
        DB::statement(<<<'SQL'
            CREATE TRIGGER trg_gps_punto
            BEFORE INSERT OR UPDATE ON registros_gps
            FOR EACH ROW EXECUTE FUNCTION gps_punto_sync();
        SQL);
    }

    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS trg_gps_punto ON registros_gps');
        DB::statement('DROP FUNCTION IF EXISTS gps_punto_sync()');
        Schema::dropIfExists('registros_gps');
    }
};
