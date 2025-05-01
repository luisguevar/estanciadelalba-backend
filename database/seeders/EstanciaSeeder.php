<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class EstanciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('estancias')->insert([
            [
                'huespedTitular_id' => 1,
                'dFechaCheckIn' => null,
                'dFechaCheckOut' => null,
                'nTotalHabitaciones' => 1,
                'nEstado' => 1, // 1 reservado 
                'cNroReserva' => '2501ADM000001', // Número de reserva
                'dFechaReservaCheckIn' => Carbon::now()->addDays(1)->toDateTimeString(),
                'dFechaReservaCheckOut' => Carbon::now()->addDays(3)->toDateTimeString(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            [
                'huespedTitular_id' => 2,
                'dFechaCheckIn' => null,
                'dFechaCheckOut' => null,
                'nTotalHabitaciones' => 1,
                'nEstado' => 1, //reservado
                'cNroReserva' => '2501ADM000002', // Número de reserva
                'dFechaReservaCheckIn' => Carbon::now()->addDays(4)->toDateTimeString(),
                'dFechaReservaCheckOut' => Carbon::now()->addDays(6)->toDateTimeString(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            [
                'huespedTitular_id' => 3,
                'dFechaCheckIn' => Carbon::now()->subDays(1)->toDateTimeString(),
                'dFechaCheckOut' => null,
                'nTotalHabitaciones' => 1,
                'nEstado' => 2, //En Estancia
                'cNroReserva' => null, // Sin Reserva
                'dFechaReservaCheckIn' => null,
                'dFechaReservaCheckOut' => Carbon::now()->addDays(2)->toDateTimeString(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'huespedTitular_id' => 4,
                'dFechaCheckIn' => Carbon::now()->subDays(5)->toDateTimeString(),
                'dFechaCheckOut' => null,
                'nTotalHabitaciones' => 1,
                'nEstado' => 2, //En Estancia
                'cNroReserva' => null, // Sin Reserva
                'dFechaReservaCheckIn' => null,
                'dFechaReservaCheckOut' => Carbon::now()->addDays(2)->toDateTimeString(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            [
                'huespedTitular_id' => 5,
                'dFechaCheckIn' => Carbon::now()->subDays(2)->toDateTimeString(),
                'dFechaCheckOut' => null,
                'nTotalHabitaciones' => 1,
                'nEstado' => 2, //En Estancia
                'cNroReserva' => null, // Sin Reserva
                'dFechaReservaCheckIn' => null,
                'dFechaReservaCheckOut' => Carbon::now()->addDays(4)->toDateTimeString(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            [
                'huespedTitular_id' => 6,
                'dFechaCheckIn' => Carbon::now()->subDays(3)->toDateTimeString(),
                'dFechaCheckOut' => null,
                'nTotalHabitaciones' => 1,
                'nEstado' => 2, //En Estancia
                'cNroReserva' => null, // Sin Reserva
                'dFechaReservaCheckIn' => null,
                'dFechaReservaCheckOut' => Carbon::now()->addDays(1)->toDateTimeString(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

        ]);
    }
}
