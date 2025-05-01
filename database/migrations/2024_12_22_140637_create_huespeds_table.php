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
        Schema::create('huespeds', function (Blueprint $table) {
            $table->id();
            $table->string('cNombres');
            $table->string('cApellidos');
            $table->string('cCorreo')->nullable();
            $table->unsignedTinyInteger('nTipoDocumento');
            $table->string('cNroDocumento')->unique(); // Asegura que sea único
            $table->string('cNroCelular')->nullable();
            $table->foreignId('pais_id')->constrained('paises')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('huespeds');
    }
};
