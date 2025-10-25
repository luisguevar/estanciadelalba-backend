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
        Schema::create('habitacion_incidencia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('habitacion_id');
            $table->text('cObservaciones')->nullable();
            $table->tinyInteger('nEstado')->default(0)->comment('0: pendiente, 1: habilitado para uso, 2: con incidencia');
            $table->unsignedBigInteger('usuarioCreacion_id')->nullable();
            $table->unsignedBigInteger('usuarioModificacion_id')->nullable();
            $table->timestamps();

            $table->foreign('habitacion_id')
                ->references('id')
                ->on('habitacions')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habitacion_incidencia');
    }
};
