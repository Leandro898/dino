<?php
$html = file_get_contents('/var/www/dino/resources/views/livewire/orders-table-realtime.blade.php');
// keep only script
preg_match('/<script>.*<\/script>/is', $html, $matches);
$html = "<div>" . $matches[0] . "</div>";

$dom = new \DOMDocument();
@$dom->loadHTML($html);
$body = $dom->getElementsByTagName('body')->item(0);
foreach ($body->childNodes as $child) {
    if ($child->nodeType == XML_ELEMENT_NODE) {
        echo "Root node: " . $child->tagName . "\n";
    }
}
