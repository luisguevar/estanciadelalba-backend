<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    use HasFactory;

    protected $fillable = [
        'nTipoComprobante',
        'nTipoDocumento',
        'cClienteNroDocumento',
        'cClienteNombre',
        'cDireccion',
        'cCorreo',
        'cTotalGravada',
        'cTotalIgv',
        'cTotal',
        'cSerie',
        'nNumero',
        'cEnlace',
        'bAceptadaPorSunat',
        'cSunatDescripcion',
        'cSunatNote',
        'cSunatResponseCode',
        'cSunatSoapCode',
        'bAnulado',
        'cCadenaParaCodigoQR',
        'cCodigoHash',
        'cCodigoBarras',
        'cKey',
        'cDigitesValue',
        'cEnlacePDF',
        'cEnlaceXML',
        'cEnlaceCDR',
        'usuarioResponsable_id'
    ];

    public function usuarioResponsable()
    {
        return $this->belongsTo(User::class, 'usuarioResponsable_id');
    }
}
