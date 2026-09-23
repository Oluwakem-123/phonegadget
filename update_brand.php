<?php
require_once __DIR__ . '/config/database.php';
$stmt = $pdo->query("UPDATE phones SET brand = 'Google Pixel' WHERE brand = 'Google'");
echo 'Updated ' . $stmt->rowCount() . ' rows.';
