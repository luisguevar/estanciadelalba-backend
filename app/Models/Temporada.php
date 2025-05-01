<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Temporada extends Model
{
    use HasFactory;

    protected $fillable = [
        'habitacion_id',
        'cNombreTemporada',
        'dFechaInicio',
        'dFechaFin',
        'nPrecio',
        'created_at',
        'updated_at'
    ];

    // En el modelo Temporada
    public function setDFechaInicioAttribute($value)
    {
        $this->attributes['dFechaInicio'] = \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function setDFechaFinAttribute($value)
    {
        $this->attributes['dFechaFin'] = \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
    }
}
