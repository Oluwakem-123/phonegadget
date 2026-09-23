<?php
require_once __DIR__ . '/config/database.php';

// Check products
$apple = $pdo->query("SELECT COUNT(*) FROM phones WHERE brand = 'Apple'")->fetchColumn();
$samsung = $pdo->query("SELECT COUNT(*) FROM phones WHERE brand = 'Samsung'")->fetchColumn();
$pixel = $pdo->query("SELECT COUNT(*) FROM phones WHERE brand = 'Google'")->fetchColumn();
$total = $pdo->query("SELECT COUNT(*) FROM phones")->fetchColumn();

echo "Apple: $apple\n";
echo "Samsung: $samsung\n";
echo "Pixel: $pixel\n";
echo "Total: $total\n";
