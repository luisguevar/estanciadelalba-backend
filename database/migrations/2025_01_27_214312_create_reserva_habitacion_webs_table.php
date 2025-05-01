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
        Schema::create('reserva_habitacion_webs', function (Blueprint $table) {
            $table->id();
            $table->integer('reservaWeb_id');
            $table->integer('habitacion_id'); 
            $table->integer('nNroHabitacion');
            $table->double('nPrecioTotal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reserva_habitacion_webs');
    }
};
