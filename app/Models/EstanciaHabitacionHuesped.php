<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstanciaHabitacionHuesped extends Model
{
    use HasFactory;

    protected $table = 'estancia_habitacion_huespeds';

    protected $fillable = [
        'huesped_id',
        'estanciaHabitacion_id',
        'nEstado',
    ];

    public function huesped()
    {
        return $this->belongsTo(Huesped::class);
    }

  

    public function estanciaHabitacion()
    {
        return $this->belongsTo(EstanciaHabitacion::class, 'estanciaHabitacion_id');
    }
}
