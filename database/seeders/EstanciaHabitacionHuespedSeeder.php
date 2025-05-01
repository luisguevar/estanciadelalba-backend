<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstanciaHabitacionHuespedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('estancia_habitacion_huespeds')->insert([
            [
                'huesped_id' => 1,
                'estanciaHabitacion_id' => 1,
                'nEstado' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'huesped_id' => 2,
                'estanciaHabitacion_id' => 2,
                'nEstado' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'huesped_id' => 3,
                'estanciaHabitacion_id' => 2,
                'nEstado' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'huesped_id' => 4,
                'estanciaHabitacion_id' => 3,
                'nEstado' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'huesped_id' => 5,
                'estanciaHabitacion_id' => 3,
                'nEstado' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],



            [
                'huesped_id' => 5,
                'estanciaHabitacion_id' => 4,
                'nEstado' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'huesped_id' => 6,
                'estanciaHabitacion_id' => 4,
                'nEstado' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],


            [
                'huesped_id' => 7,
                'estanciaHabitacion_id' => 5,
                'nEstado' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],


            [
                'huesped_id' => 8,
                'estanciaHabitacion_id' => 6,
                'nEstado' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'huesped_id' => 2,
                'estanciaHabitacion_id' => 6,
                'nEstado' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'huesped_id' => 1,
                'estanciaHabitacion_id' => 6,
                'nEstado' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'huesped_id' => 3,
                'estanciaHabitacion_id' => 7,
                'nEstado' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
