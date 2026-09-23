<?php
$url = 'https://www.apple.com/v/home/images/iphone-family/a/hero_iphone_family__be5jkzxszb1e_large.jpg';
$savePath = __DIR__ . '/assets/images/hero_premium.jpg';

// Create dir if not exists
if (!is_dir(__DIR__ . '/assets/images/')) {
    mkdir(__DIR__ . '/assets/images/', 0777, true);
}

// Download image
$imgData = file_get_contents($url);
if ($imgData === false) {
    die("Failed to download image.\n");
}

$source = imagecreatefromstring($imgData);
if (!$source) {
    die("Failed to create image from string.\n");
}

$width = imagesx($source);
$height = imagesy($source);

echo "Original Width: $width, Height: $height\n";

// We want to crop the center part. Let's assume the phones are in the center.
// For example, if it's 1000x500, we crop maybe 600x500 from the center.
$crop_width = floor($width * 0.6);
$crop_height = $height; // Keep full height, or maybe crop a bit from top/bottom?
$crop_x = floor(($width - $crop_width) / 2);
$crop_y = 0;

echo "Cropping width: $crop_width, height: $crop_height at x: $crop_x, y: $crop_y\n";

$cropped = imagecrop($source, ['x' => $crop_x, 'y' => $crop_y, 'width' => $crop_width, 'height' => $crop_height]);

if ($cropped !== false) {
    imagejpeg($cropped, $savePath, 95);
    imagedestroy($cropped);
    echo "Cropped image saved successfully to $savePath\n";
} else {
    echo "Failed to crop image.\n";
}

imagedestroy($source);
