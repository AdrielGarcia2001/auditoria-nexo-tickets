<?php

namespace Database\Seeders;

use App\Models\Sitio;
use App\Models\SupervisorTecnico;
use App\Models\SupervisorZona;
use App\Models\TecnicoSitio;
use App\Models\User;
use App\Models\Zona;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ====== usuarios ======
        $admin = User::updateOrCreate(
            ['email' => 'admin@nexo.com'],
            ['name' => 'Adriel', 'password' => 'password', 'role' => User::ROLE_ADMIN, 'activo' => true, 'password_cambiado' => true]
        );

        $supervisor = User::updateOrCreate(
            ['email' => 'supervisor@nexo.com'],
            ['name' => 'Cecilia Roldan', 'password' => 'password', 'role' => User::ROLE_SUPERVISOR, 'activo' => true, 'password_cambiado' => true]
        );

        $mant1 = User::updateOrCreate(
            ['email' => 'abel.garcia@nexo.com'],
            ['name' => 'Abel Nelson Garcia', 'password' => 'password', 'role' => User::ROLE_TECNICO, 'area' => User::AREA_MANTENIMIENTO, 'activo' => true, 'password_cambiado' => true]
        );

        $mant2 = User::updateOrCreate(
            ['email' => 'lucas.barreto@nexo.com'],
            ['name' => 'Lucas Barreto', 'password' => 'password', 'role' => User::ROLE_TECNICO, 'area' => User::AREA_MANTENIMIENTO, 'activo' => true, 'password_cambiado' => true]
        );

        $limp1 = User::updateOrCreate(
            ['email' => 'santiago.garcia@nexo.com'],
            ['name' => 'Santiago Garcia', 'password' => 'password', 'role' => User::ROLE_TECNICO, 'area' => User::AREA_LIMPIEZA, 'activo' => true, 'password_cambiado' => true]
        );

        $limp2 = User::updateOrCreate(
            ['email' => 'maria.ruiz@nexo.com'],
            ['name' => 'Maria Ruiz', 'password' => 'password', 'role' => User::ROLE_TECNICO, 'area' => User::AREA_LIMPIEZA, 'activo' => true, 'password_cambiado' => true]
        );

        // ====== sitios de ejemplo ======
        $incone = Sitio::updateOrCreate(
            ['nombre' => 'INCONE URA', 'provincia_codigo' => 'A36'],
            ['provincia_nombre' => 'FORMOSA']
        );

        $evaPeron = Sitio::updateOrCreate(
            ['nombre' => 'EVA PERON URA', 'provincia_codigo' => 'A36'],
            ['provincia_nombre' => 'FORMOSA']
        );

        // tecnicos fijos por sitio
        TecnicoSitio::updateOrCreate(['sitio_id' => $incone->id, 'tecnico_id' => $limp1->id], ['asignado_por' => $supervisor->id]);
        TecnicoSitio::updateOrCreate(['sitio_id' => $evaPeron->id, 'tecnico_id' => $limp1->id], ['asignado_por' => $supervisor->id]);
        TecnicoSitio::updateOrCreate(['sitio_id' => $incone->id, 'tecnico_id' => $mant1->id], ['asignado_por' => $supervisor->id]);
        TecnicoSitio::updateOrCreate(['sitio_id' => $incone->id, 'tecnico_id' => $mant2->id], ['asignado_por' => $supervisor->id]);

        // ====== supervisor <-> tecnicos ======
        foreach ([$mant1, $mant2, $limp1, $limp2] as $tecnico) {
            SupervisorTecnico::updateOrCreate(
                ['supervisor_id' => $supervisor->id, 'tecnico_id' => $tecnico->id],
                ['asignado_en' => now()]
            );
        }

        // ====== zonas delimitadas ======
        $zonaNorte = Zona::updateOrCreate(['codigo' => 'ZN'], [
            'nombre' => 'Zona Norte', 'descripcion' => 'Cuadrillas de la zona norte de Formosa', 'activo' => true,
        ]);
        $zonaCentro = Zona::updateOrCreate(['codigo' => 'ZC'], [
            'nombre' => 'Zona Centro', 'descripcion' => 'Cuadrillas del centro de Formosa', 'activo' => true,
        ]);
        Zona::updateOrCreate(['codigo' => 'ZS'], [
            'nombre' => 'Zona Sur', 'descripcion' => 'Cuadrillas de la zona sur de Formosa', 'activo' => true,
        ]);

        $incone->update(['zona_id' => $zonaNorte->id]);
        $evaPeron->update(['zona_id' => $zonaCentro->id]);

        // ====== supervisor <-> zonas ======
        foreach ([$zonaNorte, $zonaCentro] as $zona) {
            SupervisorZona::updateOrCreate(
                ['supervisor_id' => $supervisor->id, 'zona_id' => $zona->id],
                ['asignado_en' => now()]
            );
        }
    }
}
