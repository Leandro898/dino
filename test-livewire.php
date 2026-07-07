<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$html = Livewire\Livewire::mount('vendor-orders-table');
file_put_contents('test-livewire.html', $html);
