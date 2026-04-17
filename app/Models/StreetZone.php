<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StreetZone extends Model
{
    protected $fillable = [
        'street_name',
        'number_from',
        'number_to',
        'zone_key',
    ];

    protected $casts = [
        'number_from' => 'integer',
        'number_to'   => 'integer',
    ];
}
