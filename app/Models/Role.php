<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $table = 'roles';

    protected $fillable = [
        'cNombreRol',
        'nEstado',
    ];

    public function menuItems()
    {
        return $this->belongsToMany(MenuItem::class, 'menu_item_role')
            ->withPivot(['id', 'estado', 'bPermisoEdicion',        'bPermisoEliminacion',])
            ->withTimestamps();
    }
}
