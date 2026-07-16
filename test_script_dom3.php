<?php
$html = '<body><div></div><script>var a = "\x3Ctr class=\"foo\"\x3E\x3C/tr\x3E";</script></body>';
$dom = new \DOMDocument();
@$dom->loadHTML($html);
$body = $dom->getElementsByTagName('body')->item(0);
foreach ($body->childNodes as $child) {
    if ($child->nodeType == XML_ELEMENT_NODE) {
        echo "Root node: " . $child->tagName . "\n";
    }
}
