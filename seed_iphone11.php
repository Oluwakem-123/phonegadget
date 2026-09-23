<?php
require_once __DIR__ . '/config/database.php';

// Ensure Apple category exists
$stmt = $pdo->prepare("SELECT id FROM categories WHERE name = 'Apple'");
$stmt->execute();
$apple_id = $stmt->fetchColumn();

if (!$apple_id) {
    $pdo->exec("INSERT INTO categories (name, description) VALUES ('Apple', 'Apple iPhones')");
    $apple_id = $pdo->lastInsertId();
}

$stmt = $pdo->prepare("SELECT id FROM categories WHERE name = 'iPhone 11 Series'");
$stmt->execute();
$series_id = $stmt->fetchColumn();

if (!$series_id) {
    $pdo->exec("INSERT INTO categories (name, description) VALUES ('iPhone 11 Series', 'Apple iPhone 11 lineup')");
    $series_id = $pdo->lastInsertId();
}

// Insert iPhone 11
$stmt = $pdo->prepare("
    INSERT INTO phones (category_id, brand, model, description, price, stock_quantity, release_date, is_latest, status, created_at, updated_at) 
    VALUES (?, 'Apple', 'iPhone 11', 'A powerful and popular smartphone with dual cameras.', 350000, 50, '2019-09-20', 0, 'active', NOW(), NOW())
");
$stmt->execute([$series_id]);
$phone_id = $pdo->lastInsertId();

// Insert variants
$variants = [
    ['storage' => '64GB', 'color' => 'Black', 'price' => 350000, 'stock' => 10],
    ['storage' => '64GB', 'color' => 'White', 'price' => 350000, 'stock' => 5],
    ['storage' => '128GB', 'color' => 'Black', 'price' => 400000, 'stock' => 15],
    ['storage' => '128GB', 'color' => 'Red', 'price' => 400000, 'stock' => 2],
    ['storage' => '256GB', 'color' => 'Purple', 'price' => 450000, 'stock' => 8]
];

$v_stmt = $pdo->prepare("INSERT INTO phone_variants (phone_id, storage, color, price, stock_quantity) VALUES (?, ?, ?, ?, ?)");
foreach ($variants as $v) {
    $v_stmt->execute([$phone_id, $v['storage'], $v['color'], $v['price'], $v['stock']]);
}

echo "iPhone 11 with variants inserted successfully. Phone ID: " . $phone_id . "\n";
