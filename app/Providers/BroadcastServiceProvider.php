<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Register broadcast authentication routes using Laravel's default implementation
        // This will automatically handle private channels like private-App.Models.User.1
        Broadcast::routes(['middleware' => ['web']]);
        
        // Load channel definitions
        require base_path('routes/channels.php');
    }
}
