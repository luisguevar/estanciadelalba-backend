<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegistroIngresosSalidas extends Model
{
    use HasFactory;
    protected $table = 'registro_ingresos_salidas'; 

    // Agrega los campos permitidos para asignación masiva
    protected $fillable = [
        'cTipoMovimiento',
        'dFechaMovimiento',
        'cNroHabitacion',
        'huespedResponsable_id',
        'nNroHuespedes',
        'nMontoPagar',
        'nMontoPagado',
        'usuarioResponsable_id',
        'nEstado'
    ];

    public function usuarioResponsable()
    {
        return $this->belongsTo(User::class, 'usuarioResponsable_id');
    }

    public function huespedResponsable()
    {
        return $this->belongsTo(Huesped::class, 'huespedResponsable_id');
    }
}
