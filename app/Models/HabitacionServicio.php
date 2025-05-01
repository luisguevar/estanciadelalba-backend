<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HabitacionServicio extends Model
{
    use HasFactory;

  
    protected $table = 'habitacion_servicios'; 

   
    public $timestamps = true;

   
    public function habitacion()
    {
        return $this->belongsTo(Habitacion::class);
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }
}
