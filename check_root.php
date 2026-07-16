<?php
require '/var/www/dino/vendor/autoload.php';
$app = require_once '/var/www/dino/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$view = view('livewire.orders-table-realtime')->render();
echo "First 20 chars: " . substr(trim($view), 0, 20) . "\n";
echo "Last 20 chars: " . substr(trim($view), -20) . "\n";
