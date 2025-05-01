<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HabitacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipoHabitacionIds = DB::table('tipo_habitacions')->pluck('id', 'cNombreTipo');

        $habitaciones = [
            ['nNroHabitacion' => 300, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN SIMPLE'], 'nivelHabitacion_id' => 1, 'nEstado' => 1, 'cImagen' => 'habitaciones/simple.webp'],
            ['nNroHabitacion' => 301, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN MATRIMONIAL'], 'nivelHabitacion_id' => 1, 'nEstado' => 1, 'cImagen' => 'habitaciones/matrimonial.webp'],
            ['nNroHabitacion' => 302, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN MATRIMONIAL'], 'nivelHabitacion_id' => 1, 'nEstado' => 1, 'cImagen' => 'habitaciones/matrimonial.webp'],
            ['nNroHabitacion' => 303, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN MATRIMONIAL'], 'nivelHabitacion_id' => 1, 'nEstado' => 1, 'cImagen' => 'habitaciones/matrimonial.webp'],
            ['nNroHabitacion' => 304, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN SIMPLE'], 'nivelHabitacion_id' => 1, 'nEstado' => 1, 'cImagen' => 'habitaciones/simple.webp'],
            ['nNroHabitacion' => 305, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN SIMPLE'], 'nivelHabitacion_id' => 1, 'nEstado' => 1, 'cImagen' => 'habitaciones/simple.webp'],
            ['nNroHabitacion' => 306, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN MATRIMONIAL'], 'nivelHabitacion_id' => 1, 'nEstado' => 1, 'cImagen' => 'habitaciones/matrimonial.webp'],
            ['nNroHabitacion' => 307, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN MATRIMONIAL'], 'nivelHabitacion_id' => 1, 'nEstado' => 1, 'cImagen' => 'habitaciones/matrimonial.webp'],
            ['nNroHabitacion' => 308, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN MATRIMONIAL'], 'nivelHabitacion_id' => 1, 'nEstado' => 1, 'cImagen' => 'habitaciones/matrimonial.webp'],
            ['nNroHabitacion' => 309, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN DOBLE'], 'nivelHabitacion_id' => 1, 'nEstado' => 1, 'cImagen' => 'habitaciones/doble.webp'],
            ['nNroHabitacion' => 310, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN CUÁDRUPLE'], 'nivelHabitacion_id' => 1, 'nEstado' => 1, 'cImagen' => 'habitaciones/cuadruple.webp'],
            ['nNroHabitacion' => 311, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN CUÁDRUPLE'], 'nivelHabitacion_id' => 1, 'nEstado' => 1, 'cImagen' => 'habitaciones/cuadruple.webp'],
            ['nNroHabitacion' => 312, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN MATRIMONIAL'], 'nivelHabitacion_id' => 1, 'nEstado' => 1, 'cImagen' => 'habitaciones/matrimonial.webp'],
            ['nNroHabitacion' => 313, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN MATRIMONIAL'], 'nivelHabitacion_id' => 1, 'nEstado' => 1, 'cImagen' => 'habitaciones/matrimonial.webp'],
            ['nNroHabitacion' => 314, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN CUÁDRUPLE'], 'nivelHabitacion_id' => 1, 'nEstado' => 1, 'cImagen' => 'habitaciones/cuadruple.webp'],

            ['nNroHabitacion' => 400, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN SIMPLE'], 'nivelHabitacion_id' => 2, 'nEstado' => 1, 'cImagen' => 'habitaciones/simple.webp'],
            ['nNroHabitacion' => 401, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN SIMPLE'], 'nivelHabitacion_id' => 2, 'nEstado' => 1, 'cImagen' => 'habitaciones/simple.webp'],
            ['nNroHabitacion' => 402, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN SIMPLE'], 'nivelHabitacion_id' => 2, 'nEstado' => 1, 'cImagen' => 'habitaciones/simple.webp'],
            ['nNroHabitacion' => 403, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN SIMPLE'], 'nivelHabitacion_id' => 2, 'nEstado' => 1, 'cImagen' => 'habitaciones/simple.webp'],
            ['nNroHabitacion' => 404, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN CUÁDRUPLE'], 'nivelHabitacion_id' => 2, 'nEstado' => 1, 'cImagen' => 'habitaciones/cuadruple.webp'],
            ['nNroHabitacion' => 405, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN FAMILIAR 1'], 'nivelHabitacion_id' => 2, 'nEstado' => 1, 'cImagen' => 'habitaciones/familiar_1.webp'],
            ['nNroHabitacion' => 406, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN CUÁDRUPLE'], 'nivelHabitacion_id' => 2, 'nEstado' => 1, 'cImagen' => 'habitaciones/cuadruple.webp'],
            ['nNroHabitacion' => 407, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN MATRIMONIAL'], 'nivelHabitacion_id' => 2, 'nEstado' => 1, 'cImagen' => 'habitaciones/matrimonial.webp'],
            ['nNroHabitacion' => 408, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN FAMILIAR 2'], 'nivelHabitacion_id' => 2, 'nEstado' => 1, 'cImagen' => 'habitaciones/familiar_2.webp'],
            ['nNroHabitacion' => 409, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN SUITE'], 'nivelHabitacion_id' => 2, 'nEstado' => 1, 'cImagen' => 'habitaciones/suite.webp'],
            ['nNroHabitacion' => 410, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN DOBLE'], 'nivelHabitacion_id' => 2, 'nEstado' => 1, 'cImagen' => 'habitaciones/doble.webp'],
            ['nNroHabitacion' => 411, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN DOBLE'], 'nivelHabitacion_id' => 2, 'nEstado' => 1, 'cImagen' => 'habitaciones/doble.webp'],
            ['nNroHabitacion' => 412, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN DOBLE'], 'nivelHabitacion_id' => 2, 'nEstado' => 1, 'cImagen' => 'habitaciones/doble.webp'],
            ['nNroHabitacion' => 413, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN SUITE'], 'nivelHabitacion_id' => 2, 'nEstado' => 1, 'cImagen' => 'habitaciones/suite.webp'],

            ['nNroHabitacion' => 500, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN SIMPLE'], 'nivelHabitacion_id' => 3, 'nEstado' => 1, 'cImagen' => 'habitaciones/simple.webp'],
            ['nNroHabitacion' => 501, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN MATRIMONIAL'], 'nivelHabitacion_id' => 3, 'nEstado' => 1, 'cImagen' => 'habitaciones/matrimonial.webp'],
            ['nNroHabitacion' => 502, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN MATRIMONIAL'], 'nivelHabitacion_id' => 3, 'nEstado' => 1, 'cImagen' => 'habitaciones/matrimonial.webp'],
            ['nNroHabitacion' => 503, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN MATRIMONIAL'], 'nivelHabitacion_id' => 3, 'nEstado' => 1, 'cImagen' => 'habitaciones/matrimonial.webp'],
            ['nNroHabitacion' => 504, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN CUÁDRUPLE'], 'nivelHabitacion_id' => 3, 'nEstado' => 1, 'cImagen' => 'habitaciones/cuadruple.webp'],
            ['nNroHabitacion' => 505, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN SIMPLE'], 'nivelHabitacion_id' => 3, 'nEstado' => 1, 'cImagen' => 'habitaciones/simple.webp'],
            ['nNroHabitacion' => 506, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN MATRIMONIAL'], 'nivelHabitacion_id' => 3, 'nEstado' => 1, 'cImagen' => 'habitaciones/matrimonial.webp'],
            ['nNroHabitacion' => 507, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN SIMPLE'], 'nivelHabitacion_id' => 3, 'nEstado' => 1, 'cImagen' => 'habitaciones/simple.webp'],
            ['nNroHabitacion' => 508, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN MATRIMONIAL'], 'nivelHabitacion_id' => 3, 'nEstado' => 1, 'cImagen' => 'habitaciones/matrimonial.webp'],
            ['nNroHabitacion' => 509, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN MATRIMONIAL'], 'nivelHabitacion_id' => 3, 'nEstado' => 1, 'cImagen' => 'habitaciones/matrimonial.webp'],
            ['nNroHabitacion' => 510, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN MATRIMONIAL'], 'nivelHabitacion_id' => 3, 'nEstado' => 1, 'cImagen' => 'habitaciones/matrimonial.webp'],
            ['nNroHabitacion' => 511, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN CUÁDRUPLE'], 'nivelHabitacion_id' => 3, 'nEstado' => 1, 'cImagen' => 'habitaciones/cuadruple.webp'],
            ['nNroHabitacion' => 512, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN DOBLE'], 'nivelHabitacion_id' => 3, 'nEstado' => 1, 'cImagen' => 'habitaciones/doble.webp'],
            ['nNroHabitacion' => 513, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN DOBLE'], 'nivelHabitacion_id' => 3, 'nEstado' => 1, 'cImagen' => 'habitaciones/doble.webp'],
            ['nNroHabitacion' => 514, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN DOBLE'], 'nivelHabitacion_id' => 3, 'nEstado' => 1, 'cImagen' => 'habitaciones/doble.webp'],
            ['nNroHabitacion' => 515, 'tipoHabitacion_id' => $tipoHabitacionIds['HABITACIÓN FAMILIAR 3'], 'nivelHabitacion_id' => 3, 'nEstado' => 1, 'cImagen' => 'habitaciones/familiar_3.webp'],
        ];

        DB::table('habitacions')->insert($habitaciones);
    }
}
