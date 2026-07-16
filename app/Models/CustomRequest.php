<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;

class CustomRequest extends Model
{
    use Notifiable, HasPushSubscriptions;
    protected $fillable = [
        'session_id',
        'status',
        'quoted_price',
        'quote_description',
        'has_unread_admin',
        'has_unread_user',
    ];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }
}
