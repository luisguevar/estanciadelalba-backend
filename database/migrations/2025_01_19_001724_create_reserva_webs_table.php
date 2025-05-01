<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reserva_webs', function (Blueprint $table) {
            $table->id();
            $table->string('cResNombreTitular');              // Nombre del Titular
            $table->string('cResApellidoTitular');            // Apellido del Titular
            $table->string('cResCorreoTitular');              // Correo del Titular
            $table->string('cResNroDocumentoTitular');        // Documento de Identidad
            $table->string('cResNroCelularTitular');          // Celular del Titular
            $table->string('cResNroCelularReferencia')->nullable();       // Celular de Referencia
            $table->integer('nResNroAdultos')->default(0);    // Número de Adultos
            $table->integer('nResNroNinos')->default(0);      // Número de Niños
            $table->timestamp('dResFechaCheckIn');                 // Fecha de Check-In
            $table->timestamp('dResFechaCheckOut');                // Fecha de Check-Out
            $table->text('cResObservaciones')->nullable();    // Observaciones
            $table->integer('nTotalNoches')->default(0);      // Total de Noches
            $table->double('nTotalAPagar')->default(0)->nullable();      // Total a Pagar
            $table->integer('nEstado')->default(1);           // Estado de la Reserva
            $table->string('cCodigoPagoReferencia')->nullable();
            $table->string('cNroReservaWeb')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reserva_webs');
    }
};
