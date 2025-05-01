<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NivelHabitacion extends Model
{
    use HasFactory;

    public function habitaciones()
    {
        return $this->hasMany(Habitacion::class, 'nivelHabitacion_id');
    }
}
