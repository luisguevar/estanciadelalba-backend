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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estancia_id')->constrained('estancias')->onDelete('cascade');
            $table->timestamp('dFechaPago')->nullable();
            $table->tinyInteger('nTipoPago')->default(1); //1 parcial, 2 total
            $table->tinyInteger('nMetodoPago')->default(1); //1 efectivo, 2 tarjeta
            $table->decimal('nMontoPagado', 10, 2)->nullable();
            $table->integer('nEstado')->default(1);
            $table->string('cNroDocumentoTitular')->nullable();
            $table->string('cNombreTitular')->nullable();
            $table->string('cCorreoTitular')->nullable();
            $table->tinyInteger('nEsCulqi')->default(0);
            $table->string('cCodigoPagoReferencia')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
