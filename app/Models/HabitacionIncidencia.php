<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HabitacionIncidencia extends Model
{
    use HasFactory;

    protected $table = 'habitacion_incidencia';

    protected $fillable = [
        'habitacion_id',
        'cObservaciones',
        'nEstado',
        'usuarioCreacion_id',
        'usuarioModificacion_id',
    ];

    // Relación inversa con Habitacion
    public function habitacion()
    {
        return $this->belongsTo(Habitacion::class, 'habitacion_id');
    }

    public function imagenes()
    {
        return $this->hasMany(HabitacionIncidenciaImagen::class, 'habitacion_incidencia_id');
    }

    public function usuarioCreacion()
    {
        return $this->belongsTo(User::class, 'usuarioCreacion_id');
    }

    public function usuarioModificacion()
    {
        return $this->belongsTo(User::class, 'usuarioModificacion_id');
    }
}
