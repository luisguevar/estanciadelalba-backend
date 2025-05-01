<?php

namespace Database\Seeders;

use App\Models\Habitacion;
use App\Models\Servicio;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HabitacionServicioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todas las habitaciones y servicios
        // $habitaciones = Habitacion::all();
        // $servicios = Servicio::all();

        // // Asignar aleatoriamente servicios a las habitaciones
        // foreach ($habitaciones as $habitacion) {
        //     // Elegir un número aleatorio de servicios para asignar a esta habitación (por ejemplo, de 1 a 3 servicios)
        //     $numServicios = rand(1, 3); // Cambia el rango según lo necesites

        //     // Obtener servicios aleatorios de la lista de servicios
        //     $serviciosSeleccionados = $servicios->random($numServicios);

        //     foreach ($serviciosSeleccionados as $servicio) {
        //         // Insertar la relación en la tabla 'habitacion_servicios'
        //         DB::table('habitacion_servicios')->insert([
        //             'habitacion_id' => $habitacion->id,
        //             'servicio_id' => $servicio->id,
        //             'nEstado' => 1,  // Estado activo
        //             'created_at' => now(),
        //             'updated_at' => now(),
        //         ]);
        //     }
        // }

        $habitacion_servicios = [
            ['habitacion_id' => 1, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 1, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 1, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 2, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 2, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 2, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 3, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 3, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 3, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 4, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 4, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 4, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 5, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 5, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 5, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 6, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 6, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 6, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 7, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 7, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 7, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 8, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 8, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 8, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 9, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 9, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 9, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 10, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 10, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 10, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 11, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 11, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 11, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 12, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 12, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 12, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 13, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 13, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 13, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 14, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 14, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 14, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 15, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 15, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 15, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 16, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 16, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 16, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 17, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 17, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 17, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 18, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 18, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 18, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 19, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 19, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 19, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 20, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 20, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 20, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 21, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 21, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 21, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 22, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 22, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 22, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 23, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 23, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 23, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 24, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 24, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 24, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 25, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 25, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 25, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 26, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 26, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 26, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 27, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 27, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 27, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 28, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 28, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 28, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 29, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 29, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 29, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 30, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 30, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 30, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 31, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 31, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 31, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 32, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 32, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 32, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 33, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 33, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 33, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 34, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 34, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 34, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 35, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 35, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 35, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 36, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 36, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 36, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 37, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 37, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 37, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 38, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 38, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 38, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 39, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 39, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 39, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 40, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 40, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 40, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 41, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 41, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 41, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 42, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 42, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 42, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 43, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 43, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 43, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 44, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 44, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 44, 'servicio_id' => 3, 'nEstado' => 1],

            ['habitacion_id' => 45, 'servicio_id' => 1, 'nEstado' => 1],
            ['habitacion_id' => 45, 'servicio_id' => 2, 'nEstado' => 1],
            ['habitacion_id' => 45, 'servicio_id' => 3, 'nEstado' => 1],


        ];

        DB::table('habitacion_servicios')->insert($habitacion_servicios);
    }
}
