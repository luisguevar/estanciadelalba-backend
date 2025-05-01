<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HuespedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('huespeds')->insert([
            [
                'cNombres' => 'Juan',
                'cApellidos' => 'Pérez',
                'cCorreo' => 'juanperez@gmail.com',
                'nTipoDocumento' => 1,
                'cNroDocumento' => '78767876',
                'cNroCelular' => '987654321',
                'pais_id' => 1, // ID del país Perú
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cNombres' => 'María',
                'cApellidos' => 'Gómez',
                'cCorreo' => 'mariagomez@gmail.com',
                'nTipoDocumento' => 2,
                'cNroDocumento' => '87654321',
                'cNroCelular' => '912345678',
                'pais_id' => 2, // ID del país México
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cNombres' => 'Carlos',
                'cApellidos' => 'Ramírez',
                'cCorreo' => 'carlosramirez@gmail.com',
                'nTipoDocumento' => 1,
                'cNroDocumento' => '65432189',
                'cNroCelular' => '998765432',
                'pais_id' => 1, // ID del país Perú
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cNombres' => 'Ana',
                'cApellidos' => 'Fernández',
                'cCorreo' => 'anafernandez@gmail.com',
                'nTipoDocumento' => 2,
                'cNroDocumento' => '54321876',
                'cNroCelular' => '987654320',
                'pais_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cNombres' => 'Luis',
                'cApellidos' => 'García',
                'cCorreo' => 'luisgarcia@gmail.com',
                'nTipoDocumento' => 1,
                'cNroDocumento' => '43218765',
                'cNroCelular' => '976543219',
                'pais_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cNombres' => 'Sofía',
                'cApellidos' => 'Martínez',
                'cCorreo' => 'sofiamartinez@gmail.com',
                'nTipoDocumento' => 2,
                'cNroDocumento' => '32187654',
                'cNroCelular' => '965432198',
                'pais_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cNombres' => 'Miguel',
                'cApellidos' => 'López',
                'cCorreo' => 'miguellopez@gmail.com',
                'nTipoDocumento' => 1,
                'cNroDocumento' => '98765432',
                'cNroCelular' => '987612345',
                'pais_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cNombres' => 'Lucía',
                'cApellidos' => 'Hernández',
                'cCorreo' => 'luciahernandez@gmail.com',
                'nTipoDocumento' => 2,
                'cNroDocumento' => '12345678',
                'cNroCelular' => '987654312',
                'pais_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cNombres' => 'Andrés',
                'cApellidos' => 'Torres',
                'cCorreo' => 'andrestorres@gmail.com',
                'nTipoDocumento' => 1,
                'cNroDocumento' => '83654321',
                'cNroCelular' => '976543210',
                'pais_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cNombres' => 'Elena',
                'cApellidos' => 'Ruiz',
                'cCorreo' => 'elenaruiz@gmail.com',
                'nTipoDocumento' => 2,
                'cNroDocumento' => '76543219',
                'cNroCelular' => '965432109',
                'pais_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
