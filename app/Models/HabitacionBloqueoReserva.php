<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HabitacionBloqueoReserva extends Model
{
    // Nombre de la tabla
    protected $table = 'habitacion_bloqueo_reserva';

    // Columnas que se pueden asignar de manera masiva
    protected $fillable = [
        'habitacion_id',
        'dFechaInicio',
        'dFechaFin',
        'cMotivoBloqueo',
        'nEstado',
    ];

    // Relación con la habitación
    public function habitacion()
    {
        return $this->belongsTo(Habitacion::class);
    }

    // Scope para filtrar bloqueos activos en un rango de fechas
    public function scopeActivosEnRango($query, $fechaInicio, $fechaFin)
    {
        return $query->where('nEstado', 1)
            ->whereDate('dFechaInicio', '<=', $fechaFin)
            ->whereDate('dFechaFin', '>=', $fechaInicio);
    }

    // Accesor opcional para mostrar el motivo en mayúsculas
    public function getMotivoAttribute()
    {
        return strtoupper($this->cMotivoBloqueo);
    }

    // En el modelo 
    public function setDFechaInicioAttribute($value)
    {
        $this->attributes['dFechaInicio'] = \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function setDFechaFinAttribute($value)
    {
        $this->attributes['dFechaFin'] = \Carbon\Carbon::parse($value)->format('Y-m-d H:i:s');
    }
}
