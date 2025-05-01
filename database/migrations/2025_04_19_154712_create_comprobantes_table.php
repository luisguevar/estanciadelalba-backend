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
        Schema::create('comprobantes', function (Blueprint $table) {
            $table->id();
            $table->string('nTipoComprobante');
            $table->string('cSerie');
            $table->string('nNumero');
            $table->text('cEnlace');
            $table->boolean('bAceptadaPorSunat');
            $table->string('cSunatDescripcion')->nullable();
            $table->string('cSunatNote')->nullable();
            $table->string('cSunatResponseCode')->nullable();
            $table->string('cSunatSoapCode')->nullable();
            $table->boolean('bAnulado');
            $table->text('cCadenaParaCodigoQR');
            $table->string('cCodigoHash');
            $table->string('cCodigoBarras');
            $table->string('cKey');
            $table->string('cDigitesValue');
            $table->text('cEnlacePDF')->nullable();
            $table->text('cEnlaceXML')->nullable();
            $table->text('cEnlaceCDR')->nullable();
            $table->tinyInteger('nTipoDocumento')->nullable();
            $table->string('cClienteNroDocumento')->nullable();
            $table->string('cClienteNombre')->nullable();
            $table->text('cDireccion')->nullable();
            $table->string('cCorreo')->nullable();
            $table->string('cTotalGravada')->nullable();
            $table->string('cTotalIgv')->nullable();
            $table->string('cTotal')->nullable();
            $table->integer('usuarioResponsable_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comprobantes');
    }
};
