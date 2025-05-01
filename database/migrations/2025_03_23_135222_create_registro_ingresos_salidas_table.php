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
        Schema::create('registro_ingresos_salidas', function (Blueprint $table) {
            $table->id();
            $table->string('cTipoMovimiento');
            $table->timestamp('dFechaMovimiento');
            $table->string('cNroHabitacion');
            $table->integer('huespedResponsable_id');
            $table->integer('nNroHuespedes');
            $table->double('nMontoPagado');
            $table->double('nMontoPagar');
            $table->integer('usuarioResponsable_id');
            $table->integer('nEstado')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_ingresos_salidas');
    }
};
