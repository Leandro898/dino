<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'price',
        'image',
        'stock',
        'is_active'
    ];

    // Relación: Un producto pertenece a un usuario (vendedor)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}