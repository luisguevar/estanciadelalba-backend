<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibroReclamacion extends Model
{
    use HasFactory;
    protected $table = 'libro_reclamacions';
    protected $guarded = [];
    protected $connection = 'mysql';

    protected static function booted()
    {
        static::created(function ($libro_reclamacion) {
            $libro_reclamacion->cNroHoja = str_pad($libro_reclamacion->id, 9, '0', STR_PAD_LEFT) . '-' . now()->year;
            $libro_reclamacion->save();
        });
    }

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'dFechaRespuestaProveedor' => 'datetime'
    ];

    public function usuarioResponsable()
    {
        return $this->belongsTo(User::class, 'usuarioResponsable_id');
    }
}
