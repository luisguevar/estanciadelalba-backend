<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaisSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('paises')->insert([
            ['cNombre' => 'Perú', 'cCodigoISO' => 'PER', 'created_at' => now(), 'updated_at' => now()],
            ['cNombre' => 'México', 'cCodigoISO' => 'MEX', 'created_at' => now(), 'updated_at' => now()],
            ['cNombre' => 'Estados Unidos', 'cCodigoISO' => 'USA', 'created_at' => now(), 'updated_at' => now()],
            ['cNombre' => 'España', 'cCodigoISO' => 'ESP', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
