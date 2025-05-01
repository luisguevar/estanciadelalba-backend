<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HabitacionPrecio extends Model
{
    use HasFactory;
    protected $table = 'habitacion_precios';
    public $timestamps = true;

    public function habitacion()
    {
        return $this->belongsTo(Habitacion::class);
    }

    public function dia()
    {
        return $this->belongsTo(Dia::class);
    }
}
