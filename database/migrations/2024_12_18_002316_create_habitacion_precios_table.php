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
        Schema::create('habitacion_precios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('habitacion_id')->constrained('habitacions')->onDelete('cascade');
            $table->foreignId('dia_id')->constrained('dias')->onDelete('cascade');
            $table->decimal('nPrecio', 10, 2);
            $table->integer('nEstado')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habitacion_precios');
    }
};
