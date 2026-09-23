<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // 1. DROP all existing foreign keys to avoid incompatible column type errors during ALTER
    try { $pdo->exec("ALTER TABLE `orders` DROP FOREIGN KEY `fk_orders_user`;"); } catch(Exception $e) {}
    try { $pdo->exec("ALTER TABLE `order_items` DROP FOREIGN KEY `fk_order_items_order`;"); } catch(Exception $e) {}
    try { $pdo->exec("ALTER TABLE `order_items` DROP FOREIGN KEY `fk_order_items_phone`;"); } catch(Exception $e) {}
    try { $pdo->exec("ALTER TABLE `cart_items` DROP FOREIGN KEY `fk_cart_items_user`;"); } catch(Exception $e) {}
    try { $pdo->exec("ALTER TABLE `cart_items` DROP FOREIGN KEY `fk_cart_items_phone`;"); } catch(Exception $e) {}

    // 2. Align parent tables (users and phones) to BIGINT UNSIGNED
    $pdo->exec("ALTER TABLE `users` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT;");
    $pdo->exec("ALTER TABLE `phones` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT;");

    // 3. Align cart_items
    $pdo->exec("ALTER TABLE `cart_items` 
        MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        MODIFY `user_id` BIGINT UNSIGNED NOT NULL,
        MODIFY `phone_id` BIGINT UNSIGNED NOT NULL,
        MODIFY `quantity` INT UNSIGNED NOT NULL;");
        
    $check_exists = $pdo->query("SELECT * FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = 'phone_marketplace' AND TABLE_NAME = 'cart_items' AND CONSTRAINT_NAME = 'chk_cart_items_quantity'")->fetch();
    if (!$check_exists) {
        $pdo->exec("ALTER TABLE `cart_items` ADD CONSTRAINT `chk_cart_items_quantity` CHECK (`quantity` > 0);");
    }

    // 4. Align orders
    $pdo->exec("UPDATE `orders` SET `customer_name` = 'N/A' WHERE `customer_name` IS NULL;");
    $pdo->exec("UPDATE `orders` SET `customer_phone` = 'N/A' WHERE `customer_phone` IS NULL;");
    $pdo->exec("UPDATE `orders` SET `delivery_address` = 'N/A' WHERE `delivery_address` IS NULL;");
    $pdo->exec("UPDATE `orders` SET `delivery_city` = 'N/A' WHERE `delivery_city` IS NULL;");
    $pdo->exec("UPDATE `orders` SET `delivery_state` = 'N/A' WHERE `delivery_state` IS NULL;");

    $pdo->exec("ALTER TABLE `orders` 
        MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        MODIFY `user_id` BIGINT UNSIGNED NOT NULL,
        MODIFY `customer_name` VARCHAR(150) NOT NULL,
        MODIFY `customer_phone` VARCHAR(30) NOT NULL,
        MODIFY `delivery_address` VARCHAR(500) NOT NULL,
        MODIFY `delivery_city` VARCHAR(100) NOT NULL,
        MODIFY `delivery_state` VARCHAR(100) NOT NULL,
        MODIFY `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
        MODIFY `status` VARCHAR(30) NOT NULL DEFAULT 'pending';");

    $check_exists = $pdo->query("SELECT * FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = 'phone_marketplace' AND TABLE_NAME = 'orders' AND CONSTRAINT_NAME = 'chk_orders_total'")->fetch();
    if (!$check_exists) {
        $pdo->exec("ALTER TABLE `orders` ADD CONSTRAINT `chk_orders_total` CHECK (`total_amount` >= 0);");
    }
    
    $check_exists = $pdo->query("SELECT * FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = 'phone_marketplace' AND TABLE_NAME = 'orders' AND CONSTRAINT_NAME = 'chk_orders_status'")->fetch();
    if (!$check_exists) {
        $pdo->exec("ALTER TABLE `orders` ADD CONSTRAINT `chk_orders_status` CHECK (`status` IN ('pending', 'processing', 'completed', 'cancelled'));");
    }

    // 5. Align order_items
    $pdo->exec("ALTER TABLE `order_items` 
        MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        MODIFY `order_id` BIGINT UNSIGNED NOT NULL,
        MODIFY `phone_id` BIGINT UNSIGNED NOT NULL,
        MODIFY `quantity` INT UNSIGNED NOT NULL,
        MODIFY `unit_price` DECIMAL(12,2) NOT NULL,
        MODIFY `subtotal` DECIMAL(12,2) NOT NULL;");

    $check_exists = $pdo->query("SELECT * FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = 'phone_marketplace' AND TABLE_NAME = 'order_items' AND CONSTRAINT_NAME = 'chk_order_items_quantity'")->fetch();
    if (!$check_exists) {
        $pdo->exec("ALTER TABLE `order_items` ADD CONSTRAINT `chk_order_items_quantity` CHECK (`quantity` > 0);");
    }
    
    $check_exists = $pdo->query("SELECT * FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = 'phone_marketplace' AND TABLE_NAME = 'order_items' AND CONSTRAINT_NAME = 'chk_order_items_unit_price'")->fetch();
    if (!$check_exists) {
        $pdo->exec("ALTER TABLE `order_items` ADD CONSTRAINT `chk_order_items_unit_price` CHECK (`unit_price` >= 0);");
    }
    
    $check_exists = $pdo->query("SELECT * FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = 'phone_marketplace' AND TABLE_NAME = 'order_items' AND CONSTRAINT_NAME = 'chk_order_items_subtotal'")->fetch();
    if (!$check_exists) {
        $pdo->exec("ALTER TABLE `order_items` ADD CONSTRAINT `chk_order_items_subtotal` CHECK (`subtotal` >= 0);");
    }

    // 6. ADD Foreign Keys back strictly matching target spec
    $pdo->exec("ALTER TABLE `orders` ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;");
    $pdo->exec("ALTER TABLE `order_items` ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
    $pdo->exec("ALTER TABLE `order_items` ADD CONSTRAINT `fk_order_items_phone` FOREIGN KEY (`phone_id`) REFERENCES `phones` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;");
    $pdo->exec("ALTER TABLE `cart_items` ADD CONSTRAINT `fk_cart_items_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;");
    $pdo->exec("ALTER TABLE `cart_items` ADD CONSTRAINT `fk_cart_items_phone` FOREIGN KEY (`phone_id`) REFERENCES `phones` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;");

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    
    echo "Strict migration successful.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
}
?>
