<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoHabitacionCama extends Model
{
    use HasFactory;

    public function tipoHabitacion()
    {
        return $this->belongsTo(TipoHabitacion::class);
    }

    public function tipoCama()
    {
        return $this->belongsTo(TipoCama::class, 'tipoCama_id'); // Asegúrate de que el nombre del modelo y la clave foránea estén correctos
    }
}
