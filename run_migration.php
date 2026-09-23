<?php
require_once __DIR__ . '/config/database.php';

try {
    // 1. Convert tables to InnoDB
    $pdo->exec("ALTER TABLE `users` ENGINE=InnoDB");
    $pdo->exec("ALTER TABLE `phones` ENGINE=InnoDB");
    $pdo->exec("ALTER TABLE `cart_items` ENGINE=InnoDB");
    $pdo->exec("ALTER TABLE `orders` ENGINE=InnoDB");
    $pdo->exec("ALTER TABLE `order_items` ENGINE=InnoDB");
    $pdo->exec("ALTER TABLE `favorites` ENGINE=InnoDB");

    // 2. Add delivery columns safely
    $check = $pdo->query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'phone_marketplace' AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'customer_name'")->fetch();
    if (!$check) {
        $pdo->exec("ALTER TABLE `orders` 
            ADD COLUMN `customer_name` varchar(100) DEFAULT NULL AFTER `user_id`,
            ADD COLUMN `customer_phone` varchar(20) DEFAULT NULL AFTER `customer_name`,
            ADD COLUMN `delivery_address` text DEFAULT NULL AFTER `customer_phone`,
            ADD COLUMN `delivery_city` varchar(100) DEFAULT NULL AFTER `delivery_address`,
            ADD COLUMN `delivery_state` varchar(100) DEFAULT NULL AFTER `delivery_city`");
    }

    // 3. Add foreign keys safely
    $fkUser = $pdo->query("SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'phone_marketplace' AND TABLE_NAME = 'orders' AND CONSTRAINT_NAME = 'fk_orders_user'")->fetch();
    if (!$fkUser) {
        $pdo->exec("ALTER TABLE `orders` ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE");
    }

    $fkOrderItemsOrder = $pdo->query("SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'phone_marketplace' AND TABLE_NAME = 'order_items' AND CONSTRAINT_NAME = 'fk_order_items_order'")->fetch();
    if (!$fkOrderItemsOrder) {
        $pdo->exec("ALTER TABLE `order_items` ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE");
    }

    $fkOrderItemsPhone = $pdo->query("SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'phone_marketplace' AND TABLE_NAME = 'order_items' AND CONSTRAINT_NAME = 'fk_order_items_phone'")->fetch();
    if (!$fkOrderItemsPhone) {
        $pdo->exec("ALTER TABLE `order_items` ADD CONSTRAINT `fk_order_items_phone` FOREIGN KEY (`phone_id`) REFERENCES `phones`(`id`) ON DELETE RESTRICT");
    }

    echo "Migration successful.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
