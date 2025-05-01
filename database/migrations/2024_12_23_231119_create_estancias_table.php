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
        Schema::create('estancias', function (Blueprint $table) {
            $table->id();
           
            $table->integer('huespedTitular_id');
            $table->timestamp('dFechaCheckIn')->nullable();
            $table->timestamp('dFechaCheckOut')->nullable();
            $table->integer('nTotalHabitaciones');
            $table->integer('nEstado');
            $table->string('cNroReserva')->nullable();
            $table->timestamp('dFechaReservaCheckIn')->nullable();
            $table->timestamp('dFechaReservaCheckOut')->nullable();
            $table->integer('usuarioResponsable_id')->nullable();
            $table->text('cObservaciones')->nullable();
            
            $table->timestamps();
        });
    }

    /* ESTADOS:
        1: En Reserva
        2: En Estancia
        3: Culminada
        4: Interrumpida 
    */
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estancias');
    }
};
