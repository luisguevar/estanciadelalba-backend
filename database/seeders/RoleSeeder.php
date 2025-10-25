<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['cNombreRol' => 'Administrador', 'nEstado' => 1]);
        Role::create(['cNombreRol' => 'Recepcionista', 'nEstado' => 1]);
    }
}
