<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageView extends Model
{
    protected $fillable = [
        'url',
        'path',
        'referer',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'ip',
        'user_agent',
        'device',
        'session_id',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ─────────────────────────────────────────────────────────────

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereDate('created_at', '>=', now()->startOfMonth());
    }

    public function scopeRealUsers($query)
    {
        return $query->whereNotNull('session_id');
    }
}
