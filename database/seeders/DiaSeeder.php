<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DiaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Días de la semana
        $dias = [
            ['cNombreDia' => 'Lunes', 'nEstado' => 1,  'cAbreviado' => 'LUN', 'created_at' => now(), 'updated_at' => now()],
            ['cNombreDia' => 'Martes', 'nEstado' => 1,  'cAbreviado' => 'MAR', 'created_at' => now(), 'updated_at' => now()],
            ['cNombreDia' => 'Miércoles', 'nEstado' => 1,  'cAbreviado' => 'MIE', 'created_at' => now(), 'updated_at' => now()],
            ['cNombreDia' => 'Jueves', 'nEstado' => 1,  'cAbreviado' => 'JUE', 'created_at' => now(), 'updated_at' => now()],
            ['cNombreDia' => 'Viernes', 'nEstado' => 1,  'cAbreviado' => 'VIE', 'created_at' => now(), 'updated_at' => now()],
            ['cNombreDia' => 'Sábado', 'nEstado' => 1,  'cAbreviado' => 'SAB', 'created_at' => now(), 'updated_at' => now()],
            ['cNombreDia' => 'Domingo', 'nEstado' => 1,  'cAbreviado' => 'DOM', 'created_at' => now(), 'updated_at' => now()],
        ];

        // Insertar en la base de datos
        DB::table('dias')->insert($dias);
    }
}
