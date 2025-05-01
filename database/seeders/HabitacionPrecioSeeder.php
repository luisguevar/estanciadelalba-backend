<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Dia;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use App\Models\Habitacion;

class HabitacionPrecioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        // Definimos los precios por tipo de habitación
        $preciosPorTipo = [
            1 => 75,   // SIMPLE
            2 => 100,  // DOBLE
            3 => 120,  // CUÁDRUPLE
            4 => 160,  // MATRIMONIAL
            5 => 180,  // FAMILIAR 1
            6 => 200,  // FAMILIAR 2
            7 => 170,  // FAMILIAR 3
            8 => 220   // SUITE
        ];

        // Traer todas las habitaciones con su tipo
        $habitaciones = DB::table('habitacions')->select('id', 'tipoHabitacion_id')->get();

        $habitacion_precios = [];

        foreach ($habitaciones as $habitacion) {
            $precio = $preciosPorTipo[$habitacion->tipoHabitacion_id] ?? 0;

            for ($dia_id = 1; $dia_id <= 7; $dia_id++) {
                $habitacion_precios[] = [
                    'habitacion_id' => $habitacion->id,
                    'dia_id' => $dia_id,
                    'nPrecio' => $precio,
                    'nEstado' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insertar en la base de datos
        DB::table('habitacion_precios')->insert($habitacion_precios);
    }

    // public function run(): void
    // {

    //     $habitaciones = range(1, 45);  // Genera un arreglo del 1 al 45
    //     $precios = [
    //         75, 100, 100, 100, 70, 70, 95, 100, 100, 125, 165, 160, 95, 95, 160, 
    //         75, 75, 75, 75, 160, 200, 160, 100, 210, 230, 120, 120, 120, 220, 
    //         75, 100, 100, 100, 160, 70, 95, 70, 100, 100, 100, 165, 120, 120, 120, 220
    //     ];


    //     $habitacion_precios = [];

    //     foreach ($habitaciones as $key => $habitacion_id) {
    //         for ($dia_id = 1; $dia_id <= 7; $dia_id++) {
    //             $habitacion_precios[] = [
    //                 'habitacion_id' => $habitacion_id,
    //                 'dia_id' => $dia_id,
    //                 'nPrecio' => $precios[$key],
    //                 'nEstado' => 1,
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ];
    //         }
    //     }


    //     // Insertar en la base de datos
    //     DB::table('habitacion_precios')->insert($habitacion_precios);
    // }
}
