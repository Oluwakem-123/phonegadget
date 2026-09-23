<?php
$_SERVER['REQUEST_URI'] = '/phone_marketplace/phones/details.php?id=57';
$_SERVER['HTTP_HOST'] = 'localhost';
$_GET['id'] = 57;

ob_start();
require __DIR__ . '/phones/details.php';
$html = ob_get_clean();

echo "Length of rendered HTML: " . strlen($html) . "\n";
if (strpos($html, 'iPhone 11') !== false) {
    echo "iPhone 11 title found.\n";
}
if (strpos($html, 'variant_id') !== false) {
    echo "Variant select found.\n";
}
