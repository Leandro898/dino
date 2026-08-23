<?php
$img = imagecreatefromjpeg('public/images/rider-logo.jpg');
imagepng($img, 'public/images/rider-logo.png');
imagedestroy($img);
echo "Done";
