<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReservaWeb extends Model
{
    use HasFactory;

    // Definir las columnas que se pueden rellenar masivamente
    protected $fillable = [
        'cResNombreTitular',
        'cResApellidoTitular',
        'cResCorreoTitular',
        'cResNroDocumentoTitular',
        'cResNroCelularTitular',
        'cResNroCelularReferencia',
        'nResNroAdultos',
        'nResNroNinos',
        'dResFechaCheckIn',
        'dResFechaCheckOut',
        'cResObservaciones',
        'nTotalNoches',
        'nTotalAPagar',
        'nEstado',
        'cCodigoPagoReferencia',
        'cNroReservaWeb'
    ];

    protected $casts = [
        'dResFechaCheckIn' => 'datetime',
        'dResFechaCheckOut' => 'datetime',
    ];

    
    // Relación con ReservaHabitacionWeb
    public function habitaciones()
    {
        return $this->hasMany(ReservaHabitacionWeb::class, 'reservaWeb_id'); // Cambia 'reserva_web_id' al nombre de la columna que asocia las tablas
    }
   
}
