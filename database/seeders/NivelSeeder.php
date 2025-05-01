<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NivelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Datos de ejemplo para tipos de habitación
        $nivelHabitacion = [
            ['cNombreNivel' => 'TERCER NIVEL', 'nEstado' => 1],
            ['cNombreNivel' => 'CUARTO NIVEL', 'nEstado' => 1],
            ['cNombreNivel' => 'QUINTO NIVEL', 'nEstado' => 1],

        ];

        // Insertar los tipos de habitación en la base de datos
        DB::table('nivel_habitacions')->insert($nivelHabitacion);
    }
}
