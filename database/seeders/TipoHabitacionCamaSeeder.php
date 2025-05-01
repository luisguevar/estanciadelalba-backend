<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoHabitacionCamaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Servicios de un hotel
        $tiposCama = [
            ['tipoHabitacion_id' => 1, 'tipoCama_id' => 2,  'nEstado' => 1, 'created_at' => now(), 'updated_at' => now(), 'nCantidad'=> 1],//simple
            ['tipoHabitacion_id' => 2, 'tipoCama_id' => 2,  'nEstado' => 1, 'created_at' => now(), 'updated_at' => now(), 'nCantidad'=> 2],//doble
            ['tipoHabitacion_id' => 3, 'tipoCama_id' => 3,  'nEstado' => 1, 'created_at' => now(), 'updated_at' => now(), 'nCantidad'=> 2],//Cuadruple
            ['tipoHabitacion_id' => 4, 'tipoCama_id' => 4,  'nEstado' => 1, 'created_at' => now(), 'updated_at' => now(), 'nCantidad'=> 1],//matrimonial
            ['tipoHabitacion_id' => 5, 'tipoCama_id' => 1,  'nEstado' => 1, 'created_at' => now(), 'updated_at' => now(), 'nCantidad'=> 3],//familiar1
            ['tipoHabitacion_id' => 6, 'tipoCama_id' => 1,  'nEstado' => 1, 'created_at' => now(), 'updated_at' => now(), 'nCantidad'=> 3],//familiar2
            ['tipoHabitacion_id' => 6, 'tipoCama_id' => 2,  'nEstado' => 1, 'created_at' => now(), 'updated_at' => now(), 'nCantidad'=> 1],//familiar2
            ['tipoHabitacion_id' => 7, 'tipoCama_id' => 1,  'nEstado' => 1, 'created_at' => now(), 'updated_at' => now(), 'nCantidad'=> 1],//familiar3
            ['tipoHabitacion_id' => 7, 'tipoCama_id' => 5,  'nEstado' => 1, 'created_at' => now(), 'updated_at' => now(), 'nCantidad'=> 1],//familiar3
            ['tipoHabitacion_id' => 8, 'tipoCama_id' => 5,  'nEstado' => 1, 'created_at' => now(), 'updated_at' => now(), 'nCantidad'=> 1],//suite

        ];

        // Insertar en la base de datos
        DB::table('tipo_habitacion_camas')->insert($tiposCama);
    }
}
