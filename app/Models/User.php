<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// 1. Importa estas dos clases de Filament
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use NotificationChannels\WebPush\HasPushSubscriptions;

// 2. Agrega "implements FilamentUser"
class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    public static function boot()
    {
        parent::boot();
        \Log::debug('[User][boot] Modelo User instanciado', [
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
        ]);
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        \Log::debug('[User][__construct] Instancia creada', [
            'attributes' => $attributes
        ]);
    }
    use HasFactory, Notifiable, HasPushSubscriptions;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_approved',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // 3. Añade este método obligatorio abajo del todo
    public function canAccessPanel(Panel $panel): bool
    {
        \Log::debug('[canAccessPanel] llamado', [
            'user_id' => $this->id,
            'user_email' => $this->email,
            'user_role' => $this->role,
            'panel_id' => $panel->getId(),
        ]);
        // Ahora el único panel es 'admin', permitimos acceso a admin y vendor
        $result = in_array($this->role, ['admin', 'vendor']);
        \Log::debug('[canAccessPanel] admin result', [
            'result' => $result
        ]);
        return $result;
    }

    // Relación: Un usuario (vendedor) tiene muchos productos
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
