<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Servicios de un hotel
        $servicios = [
            ['cNombreServicio' => 'Wi-Fi', 'nEstado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['cNombreServicio' => 'Agua caliente', 'nEstado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['cNombreServicio' => 'Habitaciones con vista', 'nEstado' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['cNombreServicio' => 'Smart TV', 'nEstado' => 1, 'created_at' => now(), 'updated_at' => now()],
        ];

        // Insertar en la base de datos
        DB::table('servicios')->insert($servicios);
    }
}
