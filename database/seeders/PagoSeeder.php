<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PagoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       

        DB::table('pagos')->insert([
            [
                'estancia_id' => 1,
                'dFechaPago' => Carbon::now()->subDays(4)->toDateTimeString(),
                'nTipoPago' => 2, // 1 parcial, 2 total
                'nMetodoPago' => 1, // 1 efectivo, 2 tarjeta
                'nMontoPagado' => 140,
                'nEstado' => 1, // 0: inactivo, 1: activo
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'cNroDocumentoTitular' => '74757790',
                'cNombreTitular' => 'Luis A. Guevara Aredo',
                'cCorreoTitular' => 'lguevara@correo.com',
            ],

            [
                'estancia_id' => 2,
                'dFechaPago' => Carbon::now(),
                'nTipoPago' => 1, // 1 parcial, 2 total
                'nMetodoPago' => 2, // 1 efectivo, 2 tarjeta
                'nMontoPagado' => 100,
                'nEstado' => 1, // 0: inactivo, 1: activo
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'cNroDocumentoTitular' => '60996264',
                'cNombreTitular' => 'Jose A. Gomez Ruiz',
                'cCorreoTitular' => 'jgomez@correo.com',
            ],

            [
                'estancia_id' => 3,
                'dFechaPago' => Carbon::now()->subDays(2)->toDateTimeString(),
                'nTipoPago' => 1, // 1 parcial, 2 total
                'nMetodoPago' => 2, // 1 efectivo, 2 tarjeta
                'nMontoPagado' => 50,
                'nEstado' => 1, // 0: inactivo, 1: activo
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'cNroDocumentoTitular' => '74847474',
                'cNombreTitular' => 'Robert Ruiz Perez',
                'cCorreoTitular' => 'rRuiz@correo.com',
            ],

            [
                'estancia_id' => 3,
                'dFechaPago' => Carbon::now()->subDays(1)->toDateTimeString(),
                'nTipoPago' => 1, // 1 parcial, 2 total
                'nMetodoPago' => 2, // 1 efectivo, 2 tarjeta
                'nMontoPagado' => 50,
                'nEstado' => 1, // 0: inactivo, 1: activo
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'cNroDocumentoTitular' => '74847474',
                'cNombreTitular' => 'Robert Ruiz Perez',
                'cCorreoTitular' => 'rRuiz@correo.com',
            ],

            [
                'estancia_id' => 4,
                'dFechaPago' => Carbon::now()->subDays(5)->toDateTimeString(),
                'nTipoPago' => 2, // 1 parcial, 2 total
                'nMetodoPago' => 2, // 1 efectivo, 2 tarjeta
                'nMontoPagado' => 200,
                'nEstado' => 1, // 0: inactivo, 1: activo
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'cNroDocumentoTitular' => '74847474',
                'cNombreTitular' => 'Robert Ruiz Perez',
                'cCorreoTitular' => 'rRuiz@correo.com',
            ],

            [
                'estancia_id' => 5,
                'dFechaPago' => Carbon::now()->subDays(2)->toDateTimeString(),
                'nTipoPago' => 2, // 1 parcial, 2 total
                'nMetodoPago' => 2, // 1 efectivo, 2 tarjeta
                'nMontoPagado' => 200,
                'nEstado' => 1, // 0: inactivo, 1: activo
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'cNroDocumentoTitular' => '74847474',
                'cNombreTitular' => 'Robert Ruiz Perez',
                'cCorreoTitular' => 'rRuiz@correo.com',
            ],

            [
                'estancia_id' => 6,
                'dFechaPago' => Carbon::now()->subDays(3)->toDateTimeString(),
                'nTipoPago' => 2, // 1 parcial, 2 total
                'nMetodoPago' => 2, // 1 efectivo, 2 tarjeta
                'nMontoPagado' => 200,
                'nEstado' => 1, // 0: inactivo, 1: activo
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'cNroDocumentoTitular' => '74847474',
                'cNombreTitular' => 'Robert Ruiz Perez',
                'cCorreoTitular' => 'rRuiz@correo.com',
            ],
        ]);
        
    }
}
