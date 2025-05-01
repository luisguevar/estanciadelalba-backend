<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstanciaHabitacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('estancia_habitacions')->insert([
            [
                'estancia_id' => 1,
                'habitacion_id' => 1,
                'nTotalHuespedesHabitacion' => 2,
                'nPrecioEstanciaHabitacion' => 140,
                'nEstado' => 1, // Por ejemplo, 1 = Activa
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'estancia_id' => 2,
                'habitacion_id' => 2,
                'nTotalHuespedesHabitacion' => 2,
                'nPrecioEstanciaHabitacion' => 120,
                'nEstado' => 1, // Por ejemplo, 1 = Activa
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'estancia_id' => 3,
                'habitacion_id' => 3,
                'nTotalHuespedesHabitacion' => 2,
                'nPrecioEstanciaHabitacion' => 100,
                'nEstado' => 1, // Por ejemplo, 1 = Activa
                'created_at' => now(),
                'updated_at' => now(),
            ],


            [
                'estancia_id' => 4,
                'habitacion_id' => 4,
                'nTotalHuespedesHabitacion' => 2,
                'nPrecioEstanciaHabitacion' => 100,
                'nEstado' => 1, // Por ejemplo, 1 = Activa
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'estancia_id' => 4,
                'habitacion_id' => 5,
                'nTotalHuespedesHabitacion' => 2,
                'nPrecioEstanciaHabitacion' => 100,
                'nEstado' => 1, // Por ejemplo, 1 = Activa
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'estancia_id' => 5,
                'habitacion_id' => 6,
                'nTotalHuespedesHabitacion' => 3,
                'nPrecioEstanciaHabitacion' => 200,
                'nEstado' => 1, // Por ejemplo, 1 = Activa
                'created_at' => now(),
                'updated_at' => now(),
            ],

            [
                'estancia_id' => 6,
                'habitacion_id' => 7,
                'nTotalHuespedesHabitacion' => 1,
                'nPrecioEstanciaHabitacion' => 200,
                'nEstado' => 1, // Por ejemplo, 1 = Activa
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}
