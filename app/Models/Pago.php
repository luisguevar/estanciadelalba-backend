<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;
    protected $table = 'pagos';
    protected $fillable = [
        'estancia_id',
        'dFechaPago',
        'nTipoPago',
        'nMetodoPago',
        'nMontoPagado',
        'nEstado',
        'cNroDocumentoTitular',
        'cNombreTitular',
        'cCorreoTitular',
        'nEsCulqi',
        'cCodigoPagoReferencia'
    ];

    public function estancia()
    {
        return $this->belongsTo(Estancia::class);
    }
}
