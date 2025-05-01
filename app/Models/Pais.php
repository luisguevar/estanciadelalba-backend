<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pais extends Model
{
    protected $table = 'paises';
    protected $fillable = ['cNombre', 'cCodigoISO'];

    public function huespeds()
    {
        return $this->hasMany(Huesped::class, 'pais_id');
    }
}
