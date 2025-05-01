<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoCama extends Model
{
    use HasFactory;
    // En App\Models\TipoCama
    public function tipoHabitacionCamas()
    {
        return $this->hasMany(TipoHabitacionCama::class, 'tipoCama_id'); // Asegúrate de que la clave foránea esté correcta
    }
}
