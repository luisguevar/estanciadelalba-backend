<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItemRole extends Model
{
    protected $table = 'menu_item_role';

    protected $fillable = [
        'menu_item_id',
        'role_id',
        'estado',
        'bPermisoEdicion',
        'bPermisoEliminacion',
    ];
}
