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
        Schema::create('nivel_habitacions', function (Blueprint $table) {
            $table->id();
            $table->string('cNombreNivel')->unique();  // Nombre del tipo de habitación
            $table->integer('nEstado')->default(1);  // Estado (activo por defecto)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nivel_habitacions');
    }
};
