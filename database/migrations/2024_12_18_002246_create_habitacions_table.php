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
        Schema::create('habitacions', function (Blueprint $table) {
            $table->id();
            $table->integer('nNroHabitacion')->unique();
            $table->integer('tipoHabitacion_id');
            $table->integer('nivelHabitacion_id');
            $table->string('cImagen')->nullable();
            $table->text('cMotivoBloqueo')->nullable();
            $table->integer('nEstado')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habitacions');
    }
};
