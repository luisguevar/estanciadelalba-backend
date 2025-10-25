<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Habitacion extends Model
{
    use HasFactory;
    public $timestamps = true;

    protected $fillable = [
        'nNroHabitacion',
        'cDescripcionHabitacion',
        'tipoHabitacion_id',
        'nivelHabitacion_id',
        'nEstado',
        'cMotivoBloqueo',
        'cImagen'
    ];

    // Relación con TipoHabitacion
    public function tipoHabitacion()
    {
        return $this->belongsTo(TipoHabitacion::class, 'tipoHabitacion_id');
    }

    public function nivel()
    {
        return $this->belongsTo(NivelHabitacion::class, 'nivelHabitacion_id');
    }

    public function estancias()
    {
        return $this->hasManyThrough(Estancia::class, EstanciaHabitacion::class, 'habitacion_id', 'id', 'id', 'estancia_id')->where('estancia_habitacions.nEstado', 1);;
    }
    public function habitacionPrecios()
    {
        return $this->hasMany(HabitacionPrecio::class);
    }
    public function habitacionServicios()
    {
        return $this->hasMany(HabitacionServicio::class);
    }
    public function incidencias()
    {
        return $this->hasMany(HabitacionIncidencia::class, 'habitacion_id');
    }

    public function incidenciaActiva()
    {
        return $this->hasOne(HabitacionIncidencia::class, 'habitacion_id')
            ->whereIn('nEstado', [0, 1, 2])
            ->orderByDesc('created_at');
    }

    public function getUrlImagenAttribute()
    {
        $baseUrl = rtrim(config('app.url'), '/');
        if (str_starts_with($this->cImagen, '/storage/')) {
            return $baseUrl . $this->cImagen;
        }
        return $baseUrl . '/storage/' . ltrim($this->cImagen, '/');
    }
}
