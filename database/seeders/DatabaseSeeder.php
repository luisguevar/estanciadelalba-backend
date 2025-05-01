<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call(DiaSeeder::class);
        $this->call(ServicioSeeder::class);
        $this->call(NivelSeeder::class);
        $this->call(TipoHabitacionSeeder::class);
        $this->call(HabitacionSeeder::class);
        $this->call(HabitacionServicioSeeder::class);
        $this->call(HabitacionPrecioSeeder::class);
        $this->call(TipoCamaSeeder::class);
        $this->call(TipoHabitacionCamaSeeder::class);
        $this->call(PaisSeeder::class);
        /* $this->call(HuespedSeeder::class); */
        /* $this->call(EstanciaSeeder::class); */
        /* $this->call(EstanciaHabitacionSeeder::class); */
        /*  $this->call(EstanciaHabitacionHuespedSeeder::class); */
        /* $this->call(PagoSeeder::class); */
    }
}
