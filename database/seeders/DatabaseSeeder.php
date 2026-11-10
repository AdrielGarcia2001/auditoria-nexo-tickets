<?php

namespace Database\Seeders;

use App\Models\Sitio;
use App\Models\TecnicoSitio;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ====== usuarios ======
        $admin = User::updateOrCreate(
            ['email' => 'admin@nexo.com'],
            [
                'name' => 'Adriel',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
                'activo' => true,
            ]
        );

        $supervisor = User::updateOrCreate(
            ['email' => 'supervisor@nexo.com'],
            [
                'name' => 'Cecilia Roldan',
                'password' => 'password',
                'role' => User::ROLE_SUPERVISOR,
                'activo' => true,
            ]
        );

        $mant1 = User::updateOrCreate(
            ['email' => 'abel.garcia@nexo.com'],
            [
                'name' => 'Abel Nelson Garcia',
                'password' => 'password',
                'role' => User::ROLE_TECNICO,
                'area' => User::AREA_MANTENIMIENTO,
                'activo' => true,
            ]
        );

        $mant2 = User::updateOrCreate(
            ['email' => 'lucas.barreto@nexo.com'],
            [
                'name' => 'Lucas Barreto',
                'password' => 'password',
                'role' => User::ROLE_TECNICO,
                'area' => User::AREA_MANTENIMIENTO,
                'activo' => true,
            ]
        );

        $limp1 = User::updateOrCreate(
            ['email' => 'santiago.garcia@nexo.com'],
            [
                'name' => 'Santiago Garcia',
                'password' => 'password',
                'role' => User::ROLE_TECNICO,
                'area' => User::AREA_LIMPIEZA,
                'activo' => true,
            ]
        );

        $limp2 = User::updateOrCreate(
            ['email' => 'maria.ruiz@nexo.com'],
            [
                'name' => 'Maria Ruiz',
                'password' => 'password',
                'role' => User::ROLE_TECNICO,
                'area' => User::AREA_LIMPIEZA,
                'activo' => true,
            ]
        );

        // ====== sitios de ejemplo (llegan en masa con la importacion) ======
        $incone = Sitio::updateOrCreate(
            ['nombre' => 'INCONE URA', 'provincia_codigo' => 'A36'],
            ['provincia_nombre' => 'FORMOSA']
        );

        $evaPeron = Sitio::updateOrCreate(
            ['nombre' => 'EVA PERON URA', 'provincia_codigo' => 'A36'],
            ['provincia_nombre' => 'FORMOSA']
        );

        // limpieza: 1 tecnico fijo por sitio (Santiago Garcia en INCONE y Eva Peron)
        TecnicoSitio::updateOrCreate(
            ['sitio_id' => $incone->id, 'tecnico_id' => $limp1->id],
            ['asignado_por' => $supervisor->id]
        );
        TecnicoSitio::updateOrCreate(
            ['sitio_id' => $evaPeron->id, 'tecnico_id' => $limp1->id],
            ['asignado_por' => $supervisor->id]
        );

        // mantenimiento: hasta 2 tecnicos por sitio
        TecnicoSitio::updateOrCreate(
            ['sitio_id' => $incone->id, 'tecnico_id' => $mant1->id],
            ['asignado_por' => $supervisor->id]
        );
        TecnicoSitio::updateOrCreate(
            ['sitio_id' => $incone->id, 'tecnico_id' => $mant2->id],
            ['asignado_por' => $supervisor->id]
        );
    }
}
