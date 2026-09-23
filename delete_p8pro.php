<?php
require_once __DIR__ . '/config/database.php';
$pdo->query("DELETE FROM phones WHERE model = 'Pixel 8 Pro'");
echo 'Deleted Pixel 8 Pro';
