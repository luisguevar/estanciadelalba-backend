<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HabitacionIncidenciaImagen extends Model
{
    use HasFactory;

    protected $table = 'habitacion_incidencia_imagen';

    protected $fillable = [
        'habitacion_incidencia_id',
        'ruta_imagen',
    ];

    protected $appends = ['url_imagen'];

    public function getUrlImagenAttribute()
    {
        $baseUrl = rtrim(config('app.url'), '/'); // quita el / final si lo hay

        // Si ya empieza con /storage, solo concatenamos
        if (str_starts_with($this->ruta_imagen, '/storage/')) {
            return $baseUrl . $this->ruta_imagen;
        }

        // Si no, se lo agregamos
        return $baseUrl . '/storage/' . ltrim($this->ruta_imagen, '/');
    }


    public function incidencia()
    {
        return $this->belongsTo(HabitacionIncidencia::class, 'habitacion_incidencia_id');
    }

    public function imagenes()
    {
        return $this->hasMany(HabitacionIncidenciaImagen::class, 'habitacion_incidencia_id');
    }
}
