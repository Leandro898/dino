<?php
$html1 = '<div><script>var a = "<tr></tr>";</script></div>';
$html2 = '<div></div><script>var a = "<tr></tr>";</script>';

function test($html) {
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
}
echo "HTML1:\n"; test($html1);
echo "HTML2:\n"; test($html2);
