<?php
$html = '<div><script>//<![CDATA[
var a = "<tr></tr>";
//]]></script></div>';

$dom = new \DOMDocument();
@$dom->loadHTML($html);
$body = $dom->getElementsByTagName('body')->item(0);
$count = 0;
foreach ($body->childNodes as $child) {
    if ($child->nodeType == XML_ELEMENT_NODE) {
        if ($child->tagName !== 'script') {
            $count++;
        }
        echo "Root node: " . $child->tagName . "\n";
    }
}
echo "Livewire count: $count\n\n";
