<?php
$html = file_get_contents('/var/www/dino/resources/views/livewire/orders-table-realtime.blade.php');
// Simulate Blade render (since we only care about DOM parsing of the raw blade structure)
$html = str_replace(['@if', '@else', '@endif', '@foreach', '@endforeach', '@forelse', '@empty', '@endforelse'], '', $html);

$dom = new \DOMDocument();
@$dom->loadHTML($html);
$body = $dom->getElementsByTagName('body')->item(0);

$count = 0;
foreach ($body->childNodes as $child) {
    if ($child->nodeType == XML_ELEMENT_NODE) {
        if ($child->tagName === 'script') continue;
        $count++;
        echo "Found root node: " . $child->tagName . " class: " . $child->getAttribute('class') . "\n";
    }
}
echo "Root count: $count\n";
