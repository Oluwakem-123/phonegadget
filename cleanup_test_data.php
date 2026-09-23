<?php
require_once __DIR__ . '/config/database.php';
$pdo->exec("DELETE FROM phones WHERE brand IN ('Apple', 'Samsung')");
$pdo->exec("DELETE FROM categories WHERE name LIKE '%iPhone%' OR name LIKE '%Galaxy%' OR name IN ('Apple', 'Samsung')");
echo "Cleaned up previously added phones.\n";
