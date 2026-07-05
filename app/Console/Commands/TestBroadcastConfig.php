<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestBroadcastConfig extends Command
{
    protected $signature = 'test:broadcast-config';
    protected $description = 'Test broadcast configuration';

    public function handle()
    {
        $config = config('broadcasting');
        
        $this->info('Current broadcast configuration:');
        $this->table(['Key', 'Value'], [
            ['BROADCAST_CONNECTION', $config['default'] ?? 'N/A'],
            ['REVERB_APP_ID', config('broadcasting.connections.reverb.app_id')],
            ['REVERB_APP_KEY', config('broadcasting.connections.reverb.key')],
            ['REVERB_HOST', config('broadcasting.connections.reverb.options.host')],
            ['REVERB_PORT', config('broadcasting.connections.reverb.options.port')],
            ['REVERB_SCHEME', config('broadcasting.connections.reverb.options.scheme')],
        ]);
        
        // Test if we can broadcast
        $this->info('Testing broadcast...');
        try {
            $broadcaster = app('broadcasting')->connection();
            $this->info('Broadcaster class: ' . get_class($broadcaster));
            $this->info('✅ Broadcaster is available and working');
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
        }
    }
}
