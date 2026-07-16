<?php
$html = file_get_contents('/var/www/dino/resources/views/livewire/orders-table-realtime.blade.php');
// Stripping script and style tags to see if Livewire trips on them
$html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
$html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
preg_match_all('/<([a-zA-Z0-9\-]+)(?:>|\s[^>]*>)|<\/([a-zA-Z0-9\-]+)>/', $html, $matches, PREG_OFFSET_CAPTURE);

$depth = 0;
foreach($matches[0] as $i => $match) {
    $tag = $match[0];
    $isClosing = strpos($tag, '</') === 0;
    
    // Self-closing tags heuristic
    if (preg_match('/(?:<input|<img|<br|<hr|<meta|<link).*?>/', $tag) && !$isClosing) {
        continue; 
    }
    
    if (strpos($tag, '/>') !== false && !$isClosing) {
        continue;
    }

    if ($isClosing) {
        $depth--;
    } else {
        $depth++;
    }
    
    if ($depth == 0 && $i < count($matches[0]) - 1) {
        echo "Depth hit 0 at tag: $tag (offset {$match[1]})\n";
        echo "Next tag is: " . $matches[0][$i+1][0] . "\n";
    }
}
echo "Final depth: $depth\n";
