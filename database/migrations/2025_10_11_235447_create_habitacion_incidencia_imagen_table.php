<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('habitacion_incidencia_imagen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('habitacion_incidencia_id');
            $table->string('ruta_imagen'); // Ejemplo: /storage/incidencias/2025-10/img1.jpg
            $table->timestamps();

            // Relación con habitacion_incidencia
            $table->foreign('habitacion_incidencia_id')
                ->references('id')
                ->on('habitacion_incidencia')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('habitacion_incidencia_imagen');
    }
};
