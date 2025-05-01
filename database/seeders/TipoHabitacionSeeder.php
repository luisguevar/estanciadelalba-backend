<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoHabitacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Datos de ejemplo para tipos de habitación
        $tiposHabitacion = [
            ['cNombreTipo' => 'HABITACIÓN SIMPLE', 'nAforo' => 1, 'nEstado' => 1], //1
            ['cNombreTipo' => 'HABITACIÓN DOBLE', 'nAforo' => 2, 'nEstado' => 1], //2
            ['cNombreTipo' => 'HABITACIÓN CUÁDRUPLE', 'nAforo' => 4, 'nEstado' => 1], //3
            ['cNombreTipo' => 'HABITACIÓN MATRIMONIAL', 'nAforo' => 2, 'nEstado' => 1], //4
            ['cNombreTipo' => 'HABITACIÓN FAMILIAR 1', 'nAforo' => 6, 'nEstado' => 1], //5
            ['cNombreTipo' => 'HABITACIÓN FAMILIAR 2', 'nAforo' => 7, 'nEstado' => 1], //6
            ['cNombreTipo' => 'HABITACIÓN FAMILIAR 3', 'nAforo' => 4, 'nEstado' => 1], //7
            ['cNombreTipo' => 'HABITACIÓN SUITE', 'nAforo' => 2, 'nEstado' => 1], //8
        ];

        // Insertar los tipos de habitación en la base de datos
        DB::table('tipo_habitacions')->insert($tiposHabitacion);
    }
}
