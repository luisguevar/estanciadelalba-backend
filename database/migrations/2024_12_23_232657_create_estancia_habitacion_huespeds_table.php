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
        Schema::create('estancia_habitacion_huespeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('huesped_id')->constrained('huespeds')->onDelete('cascade');
            $table->foreignId('estanciaHabitacion_id')->constrained('estancia_habitacions')->onDelete('cascade');
            $table->integer('nEstado'); // Estado del huésped en la estancia
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estancia_habitacion_huespeds');
    }
};
