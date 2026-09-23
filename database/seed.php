<?php
// Script to seed the database with test data
require_once __DIR__ . '/../config/database.php';

try {
    // 1. Insert Categories
    $categories = [
        'Smartphones',
        'Feature Phones',
        'Tablets'
    ];
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE TABLE categories");
    $pdo->exec("TRUNCATE TABLE phones");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    foreach ($categories as $cat) {
        $stmt->execute([$cat, "A collection of $cat"]);
    }
    
    // Get the Smartphone category ID
    $smartphone_id = $pdo->query("SELECT id FROM categories WHERE name = 'Smartphones'")->fetchColumn();

    // 2. Insert Phones
    $phones = [
        [
            'category_id' => $smartphone_id,
            'brand' => 'Apple',
            'model' => 'iPhone 15 Pro Max',
            'description' => 'The ultimate iPhone featuring a titanium design, A17 Pro chip, and advanced camera system.',
            'price' => 1850000,
            'stock_quantity' => 25,
            'image' => null, // Will use placeholder
            'release_date' => '2023-09-22',
            'is_latest' => 1,
            'status' => 'available'
        ],
        [
            'category_id' => $smartphone_id,
            'brand' => 'Apple',
            'model' => 'iPhone 13',
            'description' => 'A great balance of performance and value with A15 Bionic and dual cameras.',
            'price' => 850000,
            'stock_quantity' => 15,
            'image' => null,
            'release_date' => '2021-09-24',
            'is_latest' => 0,
            'status' => 'available'
        ],
        [
            'category_id' => $smartphone_id,
            'brand' => 'Samsung',
            'model' => 'Galaxy S24 Ultra',
            'description' => 'Premium Android experience with S-Pen, AI features, and 200MP camera.',
            'price' => 1700000,
            'stock_quantity' => 30,
            'image' => null,
            'release_date' => '2024-01-31',
            'is_latest' => 1,
            'status' => 'available'
        ],
        [
            'category_id' => $smartphone_id,
            'brand' => 'Samsung',
            'model' => 'Galaxy A55',
            'description' => 'Solid mid-range phone with premium build and reliable performance.',
            'price' => 520000,
            'stock_quantity' => 50,
            'image' => null,
            'release_date' => '2024-03-15',
            'is_latest' => 1,
            'status' => 'available'
        ],
        [
            'category_id' => $smartphone_id,
            'brand' => 'Google',
            'model' => 'Pixel 8 Pro',
            'description' => 'Incredible camera capabilities powered by Google AI.',
            'price' => 1200000,
            'stock_quantity' => 0,
            'image' => null,
            'release_date' => '2023-10-12',
            'is_latest' => 1,
            'status' => 'out_of_stock'
        ],
        [
            'category_id' => $smartphone_id,
            'brand' => 'Xiaomi',
            'model' => 'Redmi Note 13 Pro',
            'description' => 'Exceptional value with high-refresh rate screen and fast charging.',
            'price' => 350000,
            'stock_quantity' => 100,
            'image' => null,
            'release_date' => '2023-09-21',
            'is_latest' => 0,
            'status' => 'available'
        ],
        [
            'category_id' => $smartphone_id,
            'brand' => 'Tecno',
            'model' => 'Camon 30 Premier',
            'description' => 'Impressive camera-centric smartphone for the budget conscious.',
            'price' => 450000,
            'stock_quantity' => 45,
            'image' => null,
            'release_date' => '2024-04-10',
            'is_latest' => 1,
            'status' => 'available'
        ],
        [
            'category_id' => $smartphone_id,
            'brand' => 'Infinix',
            'model' => 'Note 40 Pro+',
            'description' => 'Lightning fast charging and curved display.',
            'price' => 380000,
            'stock_quantity' => 0,
            'image' => null,
            'release_date' => '2024-03-18',
            'is_latest' => 0,
            'status' => 'out_of_stock'
        ]
    ];

    $stmt = $pdo->prepare("INSERT INTO phones (category_id, brand, model, description, price, stock_quantity, image, release_date, is_latest, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($phones as $p) {
        $stmt->execute([
            $p['category_id'],
            $p['brand'],
            $p['model'],
            $p['description'],
            $p['price'],
            $p['stock_quantity'],
            $p['image'],
            $p['release_date'],
            $p['is_latest'],
            $p['status']
        ]);
    }
    
    echo "Successfully seeded the database with categories and phones.";
} catch (PDOException $e) {
    echo "Seeding failed: " . $e->getMessage();
}
