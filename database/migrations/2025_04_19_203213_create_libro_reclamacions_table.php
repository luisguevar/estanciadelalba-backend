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
        Schema::create('libro_reclamacions', function (Blueprint $table) {
            $table->id();
            $table->string('cNroHoja')->nullable();
            $table->string('cNombreReclamante');
            $table->string('cDomicilioReclamante');
            $table->integer('nTipoDocumentoReclamante')->default(1); //1=DNI, 2=CE, 3=RUC, 4=PASAPORTE
            $table->string('cNroDocumentoReclamante');
            $table->string('cTelefonoReclamante');
            $table->string('cEmailReclamante');
            $table->string('cApoderadoReclamante')->nullable(); //Solo para mayores de 18 años
            $table->enum('nTipoBien', [0, 1])->default(0); //0=Producto, 1=Servicio
            $table->decimal('nMontoReclamadoBien', 8, 2);
            $table->string('cDescripcionBien');
            $table->enum('nTipoDisconformidad', [0, 1])->default(0); //0=Queja, 1=Reclamo
            $table->text('cDetalleDisconformidad');
            $table->text('cPedidoDisconformidad');
            $table->text('cRespuestaProveedor')->nullable();
            $table->timestamp('dFechaRespuestaProveedor')->nullable();
            $table->integer('nEstadoReclamacion')->default(0); //0=Pendiente, 1=No_atendido, 2=Atendido
            $table->enum('nMoneda', [0, 1])->default(0); //0=pen, 1=usd
            $table->integer('usuarioResponsable_id')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('libro_reclamacions');
    }
};
