<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Huesped extends Model
{
    protected $table = 'huespeds'; 
    protected $fillable = ['cNombres', 'cApellidos', 'cCorreo', 'nTipoDocumento', 'cNroDocumento', 'cNroCelular', 'pais_id'];

    public function pais()
    {
        return $this->belongsTo(Pais::class, 'pais_id');
    }
    public function estanciaHabitaciones()
    {
        return $this->hasMany(EstanciaHabitacionHuesped::class, 'huesped_id');
    }
}
