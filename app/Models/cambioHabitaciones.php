<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cambioHabitaciones extends Model
{
    use HasFactory;

    protected $table = 'cambio_habitaciones';

    protected $fillable = [
        'estanciaHabitacionOrigen_id',
        'nNroHabitacionOrigen',
        'estanciaHabitacionDestino_id',
        'nPrecioEstanciaHabitacion',
        'nNroHabitacionDestino',
        'dFechaCambio',
        'cObservaciones',
        'bCambioPrecio',
        'nEstadoEstancia',
        'usuarioResponsable_id',
        'huespedResponsable_id',

        'nEstado',
    ];

    public function usuarioResponsable()
    {
        return $this->belongsTo(User::class, 'usuarioResponsable_id');
    }

    public function huespedResponsable()
    {
        return $this->belongsTo(Huesped::class, 'huespedResponsable_id');
    }
}
