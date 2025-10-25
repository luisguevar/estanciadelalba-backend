<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComprobanteDetalle extends Model
{
    protected $table = 'comprobante_detalle';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'comprobante_id',
        'nCantidad',
        'cDescripcion',
        'cNroHabitacion',
        'cUnidadMedida',
        'cSubTotal',
    ];
}
