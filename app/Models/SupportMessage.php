<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    protected $fillable = [
        'delivery_user_id',
        'sender_id',
        'message',
        'is_read_by_admin',
        'is_read_by_delivery',
    ];

    protected $casts = [
        'is_read_by_admin' => 'boolean',
        'is_read_by_delivery' => 'boolean',
    ];

    public function deliveryUser()
    {
        return $this->belongsTo(User::class, 'delivery_user_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
