<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['icon', 'title', 'link', 'estado'];

    public function items()
    {
        return $this->hasMany(MenuItem::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'menu_role');
    }
}
