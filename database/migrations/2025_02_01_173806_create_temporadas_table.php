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
        Schema::create('temporadas', function (Blueprint $table) {
            $table->id();
            $table->integer('habitacion_id'); 
            $table->string('cNombreTemporada')->nullable();
            $table->date('dFechaInicio')->nullable();
            $table->date('dFechaFin')->nullable();
            $table->decimal('nPrecio', 10, 2)->nullable();
            $table->integer('nEstado')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporadas');
    }
};
