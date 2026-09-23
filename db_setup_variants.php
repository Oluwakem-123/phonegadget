<?php
require_once __DIR__ . '/config/database.php';

try {
    // Backup step
    $pdo->exec("CREATE TABLE IF NOT EXISTS phones_backup AS SELECT * FROM phones");
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories_backup AS SELECT * FROM categories");
    echo "Backup created successfully.\n";

    // Variant changes
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS phone_variants (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            phone_id BIGINT UNSIGNED NOT NULL,
            storage VARCHAR(50) NULL,
            color VARCHAR(50) NULL,
            price DECIMAL(12,2) NOT NULL,
            stock_quantity INT UNSIGNED NOT NULL DEFAULT 0,
            FOREIGN KEY (phone_id) REFERENCES phones(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");

    // Add variant_id to cart_items if it doesn't exist
    $check_cart = $pdo->query("SHOW COLUMNS FROM cart_items LIKE 'variant_id'")->fetch();
    if (!$check_cart) {
        $pdo->exec("ALTER TABLE cart_items ADD COLUMN variant_id BIGINT UNSIGNED NULL AFTER phone_id");
        $pdo->exec("ALTER TABLE cart_items ADD FOREIGN KEY (variant_id) REFERENCES phone_variants(id) ON DELETE SET NULL");
    }

    // Add variant_id to order_items if it doesn't exist
    $check_order = $pdo->query("SHOW COLUMNS FROM order_items LIKE 'variant_id'")->fetch();
    if (!$check_order) {
        $pdo->exec("ALTER TABLE order_items ADD COLUMN variant_id BIGINT UNSIGNED NULL AFTER phone_id");
        $pdo->exec("ALTER TABLE order_items ADD FOREIGN KEY (variant_id) REFERENCES phone_variants(id) ON DELETE SET NULL");
    }

    echo "Variant database schema updated successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
