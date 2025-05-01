<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstanciaHabitacion extends Model
{
    use HasFactory;

    protected $table = 'estancia_habitacions';

    protected $fillable = [
        'estancia_id',
        'habitacion_id',
        'nTotalHuespedesHabitacion',
        'nPrecioEstanciaHabitacion',
        'nEstado',
    ];

    public function estancia()
    {
        return $this->belongsTo(Estancia::class);
    }

    public function huespedes()
    {
        return $this->hasMany(EstanciaHabitacionHuesped::class, 'estanciaHabitacion_id');
    }
    public function habitacion()
    {
        return $this->belongsTo(Habitacion::class);
    }

   

  
}
