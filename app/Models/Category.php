<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'keywords',
        'is_active',
    ];

    protected $casts = [
        'keywords' => 'array',
        'is_active' => 'boolean',
    ];
}
