<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoHabitacion extends Model
{
    use HasFactory;
    // Especificar los atributos que son asignables en masa
    protected $fillable = [
        'cNombreTipo',
        'nAforo'
    ];

    // En App\Models\TipoHabitacion
    // En App\Models\TipoHabitacion
    public function tipoHabitacionCamas()
    {
        // Asegúrate de que el nombre de la clave foránea se especifique correctamente
        return $this->hasMany(TipoHabitacionCama::class, 'tipoHabitacion_id'); // Especifica la clave foránea correcta
    }
}
