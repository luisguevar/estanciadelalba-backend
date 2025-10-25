<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = ['menu_id', 'title', 'link'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'menu_item_role')
             ->withPivot(['id', 'estado', 'bPermisoEdicion',        'bPermisoEliminacion',])
            ->withTimestamps();
    }


    public function menu()
    {
        return $this->belongsTo(Menu::class)->without('items');
    }
}
