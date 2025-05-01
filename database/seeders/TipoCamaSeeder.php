<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoCamaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Servicios de un hotel
        $tiposCama = [
            ['cNombreTipo' => 'Cama Doble', 'nEstado' => 1, 'created_at' => now(), 'updated_at' => now()], //!1
            ['cNombreServicio' => 'Cama Individual', 'nEstado' => 1, 'created_at' => now(), 'updated_at' => now()], //!2
            ['cNombreServicio' => 'Camarote', 'nEstado' => 1, 'created_at' => now(), 'updated_at' => now()], //!3
            ['cNombreServicio' => 'Cama Queen', 'nEstado' => 1, 'created_at' => now(), 'updated_at' => now()], //!4
            ['cNombreServicio' => 'Cama King', 'nEstado' => 1, 'created_at' => now(), 'updated_at' => now()], //!5
        ];

        // Insertar en la base de datos
        DB::table('tipo_camas')->insert($tiposCama);
    }
}
