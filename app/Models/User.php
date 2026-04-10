<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// 1. Importa estas dos clases de Filament
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

// 2. Agrega "implements FilamentUser"
class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
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
        // Por ahora devuelve true para poder entrar y probar.
        // Más adelante puedes poner algo como: return str_ends_with($this->email, '@tudominio.com');
        return true;
    }
}
