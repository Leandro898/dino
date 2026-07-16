<?php
$html = '<body><div></div><script>var a = "<tr class=\"foo\"></tr>";</script></body>';
$dom = new \DOMDocument();
@$dom->loadHTML($html);
$body = $dom->getElementsByTagName('body')->item(0);
foreach ($body->childNodes as $child) {
    if ($child->nodeType == XML_ELEMENT_NODE) {
        echo "Root node: " . $child->tagName . "\n";
    }
}
