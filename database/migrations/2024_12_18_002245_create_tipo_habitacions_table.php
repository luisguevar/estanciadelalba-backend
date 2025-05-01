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
        Schema::create('tipo_habitacions', function (Blueprint $table) {
            $table->id();  // ID auto incremental
            $table->string('cNombreTipo')->unique();  // Nombre del tipo de habitación
            $table->integer('nEstado')->default(1);  // Estado (activo por defecto)
            $table->integer('nAforo')->default(1);
            $table->timestamps();  // Timestamps para created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_habitacions');
    }
};
