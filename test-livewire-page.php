<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$html = Livewire\Livewire::mount('app.filament.pages.vendor-orders');
$dom = new \DOMDocument();
@$dom->loadHTML($html);
$body = $dom->getElementsByTagName('body')->item(0);
$count = 0;
foreach ($body->childNodes as $child) {
    if ($child->nodeType == XML_ELEMENT_NODE) {
        if ($child->tagName === 'script') continue;
        $count++;
        echo "Root Element: " . $child->tagName . "\n";
    }
}
echo "Count: $count\n";
