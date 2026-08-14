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
        'is_masivo',
        'opening_time',
        'closing_time',
        'opening_time_2',
        'closing_time_2',
        'closed_days',
        'banner',
        'logo',
        'address',
        'latitude',
        'longitude',
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
            'is_masivo' => 'boolean',
            'closed_days' => 'array',
        ];
    }

    public function isOpen(): bool
    {
        // 1. Verificar si hoy es un día en el que el comercio permanece cerrado
        $currentDay = now()->format('l'); // Ej: 'Sunday', 'Monday', etc.
        if (is_array($this->closed_days) && in_array($currentDay, $this->closed_days, true)) {
            return false;
        }

        // 2. Verificar horarios de atención
        // Si no está configurado ningún horario de ningún turno, por defecto está abierto
        if (is_null($this->opening_time) && is_null($this->closing_time) && is_null($this->opening_time_2) && is_null($this->closing_time_2)) {
            return true;
        }

        $now = now()->format('H:i:s');

        // Turno 1
        $shift1Open = false;
        if (!is_null($this->opening_time) && !is_null($this->closing_time)) {
            $open = $this->opening_time;
            $close = $this->closing_time;
            if ($open <= $close) {
                $shift1Open = $now >= $open && $now <= $close;
            } else {
                $shift1Open = $now >= $open || $now <= $close;
            }
        }

        // Turno 2
        $shift2Open = false;
        if (!is_null($this->opening_time_2) && !is_null($this->closing_time_2)) {
            $open2 = $this->opening_time_2;
            $close2 = $this->closing_time_2;
            if ($open2 <= $close2) {
                $shift2Open = $now >= $open2 && $now <= $close2;
            } else {
                $shift2Open = $now >= $open2 || $now <= $close2;
            }
        }

        return $shift1Open || $shift2Open;
    }

    public function getFormattedOpeningHoursAttribute(): string
    {
        $shifts = [];
        if (!is_null($this->opening_time) && !is_null($this->closing_time)) {
            $open = \Carbon\Carbon::createFromFormat('H:i:s', $this->opening_time)->format('H:i');
            $close = \Carbon\Carbon::createFromFormat('H:i:s', $this->closing_time)->format('H:i');
            $shifts[] = "{$open} a {$close}";
        }

        if (!is_null($this->opening_time_2) && !is_null($this->closing_time_2)) {
            $open2 = \Carbon\Carbon::createFromFormat('H:i:s', $this->opening_time_2)->format('H:i');
            $close2 = \Carbon\Carbon::createFromFormat('H:i:s', $this->closing_time_2)->format('H:i');
            $shifts[] = "{$open2} a {$close2}";
        }

        $hours = empty($shifts) ? 'Abierto las 24 hs' : implode(' y ', $shifts) . ' hs';

        if (is_array($this->closed_days) && !empty($this->closed_days)) {
            $dayMap = [
                'Monday' => 'Lunes',
                'Tuesday' => 'Martes',
                'Wednesday' => 'Miércoles',
                'Thursday' => 'Jueves',
                'Friday' => 'Viernes',
                'Saturday' => 'Sábado',
                'Sunday' => 'Domingo',
            ];
            $translatedDays = collect($this->closed_days)
                ->map(fn($day) => $dayMap[$day] ?? $day)
                ->implode(', ');
            $hours .= " (Cerrado: {$translatedDays})";
        }

        return $hours;
    }

    // 3. Añade este método obligatorio abajo del todo
    public function canAccessPanel(Panel $panel): bool
    {
        $panelId = $panel->getId();

        \Log::debug('[canAccessPanel] llamado', [
            'user_id' => $this->id,
            'user_role' => $this->role,
            'panel_id' => $panelId,
        ]);

        if ($panelId === 'admin') {
            return $this->role === 'admin';
        }

        if ($panelId === 'seller') {
            return in_array($this->role, ['admin', 'vendor']);
        }

        return false;
    }

    public function isOnline(): bool
    {
        if ($this->role !== 'delivery') {
            return false;
        }
        return \Illuminate\Support\Facades\Cache::has('rider_online_' . $this->id);
    }

    // Relación: Un usuario (vendedor) tiene muchos productos
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Relación: Soporte
    public function supportMessages()
    {
        return $this->hasMany(SupportMessage::class, 'delivery_user_id');
    }

    /**
     * Send the email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \App\Notifications\VerifyEmailNotification);
    }
    public function getBannerUrlAttribute(): ?string
    {
        if (blank($this->banner)) {
            return null;
        }

        if (filter_var($this->banner, FILTER_VALIDATE_URL)) {
            return $this->banner;
        }

        return asset('storage/' . ltrim($this->banner, '/'));
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (blank($this->logo)) {
            return null;
        }

        if (filter_var($this->logo, FILTER_VALIDATE_URL)) {
            return $this->logo;
        }

        return asset('storage/' . ltrim($this->logo, '/'));
    }
}
