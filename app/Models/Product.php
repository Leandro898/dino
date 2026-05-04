<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'price',
        'image',
        'stock',
        'is_active',
        'is_raffle',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_raffle' => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function (Product $product) {
            if (empty($product->slug) || $product->isDirty('name')) {
                $product->slug = static::generateUniqueSlug($product->name, $product->id ?? null);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function generateUniqueSlug(string $name, ?int $exceptId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'producto';
        $slug = $baseSlug;
        $suffix = 1;

        while (static::where('slug', $slug)
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->exists()) {
            $slug = $baseSlug . '-' . $suffix++;
        }

        return $slug;
    }

    // Relación: Un producto pertenece a un usuario (vendedor)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación: Un producto puede tener muchos items de orden (en diferentes órdenes)
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isRaffle(): bool
    {
        return (bool) $this->is_raffle;
    }
}