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
        Schema::create('estancia_habitacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estancia_id')->constrained('estancias')->onDelete('cascade');
            $table->integer('habitacion_id'); // Asume que hay una tabla de habitaciones
            $table->integer('nTotalHuespedesHabitacion');
            $table->integer('nPrecioEstanciaHabitacion');
            $table->integer('nEstado'); // Estado de la habitación
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estancia_habitacions');
    }
};
