<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservaHabitacionWeb extends Model
{
    use HasFactory;

    // Agrega los campos permitidos para asignación masiva
    protected $fillable = [
        'reservaWeb_id',
        'habitacion_id',
        'nNroHabitacion',
        'nPrecioTotal',
    ];

    public function habitacion()
    {
        return $this->belongsTo(Habitacion::class, 'habitacion_id'); // Cambia 'habitacion_id' si usas otro nombre de columna
    }
}
