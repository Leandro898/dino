<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = [
        'custom_request_id',
        'sender_type',
        'message',
        'is_system_message',
    ];

    protected $casts = [
        'is_system_message' => 'boolean',
    ];

    public function customRequest()
    {
        return $this->belongsTo(CustomRequest::class);
    }
}
