<?php
require_once __DIR__ . '/config/database.php';

// Ensure Samsung category exists
$stmt = $pdo->prepare("SELECT id FROM categories WHERE name = 'Samsung'");
$stmt->execute();
$samsung_id = $stmt->fetchColumn();

if (!$samsung_id) {
    $pdo->exec("INSERT INTO categories (name, description) VALUES ('Samsung', 'Samsung Galaxy Smartphones')");
    $samsung_id = $pdo->lastInsertId();
}

$stmt = $pdo->prepare("SELECT id FROM categories WHERE name = 'Galaxy S20 Series'");
$stmt->execute();
$series_id = $stmt->fetchColumn();

if (!$series_id) {
    $pdo->exec("INSERT INTO categories (name, description) VALUES ('Galaxy S20 Series', 'Samsung Galaxy S20 lineup')");
    $series_id = $pdo->lastInsertId();
}

// Insert Galaxy S20
$stmt = $pdo->prepare("
    INSERT INTO phones (category_id, brand, model, description, price, stock_quantity, release_date, is_latest, status, created_at, updated_at) 
    VALUES (?, 'Samsung', 'Galaxy S20', 'Samsung flagship with 120Hz display and powerful cameras.', 300000, 40, '2020-03-06', 0, 'active', NOW(), NOW())
");
$stmt->execute([$series_id]);
$phone_id = $pdo->lastInsertId();

// Insert variants
$variants = [
    ['storage' => '128GB', 'color' => 'Cosmic Grey', 'price' => 300000, 'stock' => 15],
    ['storage' => '128GB', 'color' => 'Cloud Blue', 'price' => 300000, 'stock' => 10],
    ['storage' => '256GB', 'color' => 'Cosmic Grey', 'price' => 350000, 'stock' => 5]
];

$v_stmt = $pdo->prepare("INSERT INTO phone_variants (phone_id, storage, color, price, stock_quantity) VALUES (?, ?, ?, ?, ?)");
foreach ($variants as $v) {
    $v_stmt->execute([$phone_id, $v['storage'], $v['color'], $v['price'], $v['stock']]);
}

echo "Galaxy S20 with variants inserted successfully. Phone ID: " . $phone_id . "\n";
