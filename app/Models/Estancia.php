<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estancia extends Model
{
    protected $fillable = [
        'huespedTitular_id',
        'dFechaCheckIn',
        'dFechaCheckOut',
        'nTotalHabitaciones',
        'nEstado',
        'cNroReserva',
        'dFechaReservaCheckIn',
        'dFechaReservaCheckOut',
        'cObservaciones',
        'usuarioResponsable_id'
    ];

    public function huespedTitular()
    {
        return $this->belongsTo(Huesped::class, 'huespedTitular_id');
    }

    public function usuarioResponsable()
    {
        return $this->belongsTo(User::class, 'usuarioResponsable_id');
    }


    public function habitaciones()
    {
        return $this->hasMany(EstanciaHabitacion::class, 'estancia_id');
    }

    public function estanciaHabitaciones()
    {
        return $this->hasMany(EstanciaHabitacion::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'estancia_id');
    }

    
    
}
