<?php
$html = file_get_contents('/var/www/dino/resources/views/livewire/orders-table-realtime.blade.php');
$html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
$html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
preg_match_all('/<([a-zA-Z0-9\-]+)(?:>|\s[^>]*>)|<\/([a-zA-Z0-9\-]+)>/', $html, $matches);

$stack = [];
foreach($matches[0] as $i => $tag) {
    $isClosing = strpos($tag, '</') === 0;
    $tagName = strtolower($isClosing ? $matches[2][$i] : $matches[1][$i]);
    
    if (in_array($tagName, ['input', 'img', 'br', 'hr', 'meta', 'link', 'source', 'path'])) continue;
    if (strpos($tag, '/>') !== false && !$isClosing) continue;

    if ($isClosing) {
        $last = array_pop($stack);
        if ($last !== $tagName) {
            echo "Mismatch: expected </$last> but got </$tagName> for tag $tag\n";
        }
    } else {
        $stack[] = $tagName;
    }
}
echo "Unclosed tags: " . implode(', ', $stack) . "\n";
