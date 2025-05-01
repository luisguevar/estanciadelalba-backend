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
        Schema::create('cambio_habitaciones', function (Blueprint $table) {
            $table->id();
            $table->integer('estanciaHabitacionOrigen_id');
            $table->integer('nNroHabitacionOrigen');
            $table->integer('estanciaHabitacionDestino_id');
            $table->integer('nNroHabitacionDestino');
            $table->timestamp('dFechaCambio');
            $table->text('cObservaciones')->nullable();
            $table->tinyInteger('bCambioPrecio')->default(0);
            $table->integer('nEstadoEstancia');
            $table->integer('usuarioResponsable_id');
            $table->integer('huespedResponsable_id');
            
            $table->integer('nEstado')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cambio_habitaciones');
    }
};
