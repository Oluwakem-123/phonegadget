<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // ==========================================
    // 1. MIGRATE USERS TABLE
    // ==========================================
    
    // First safely map the enum values. Catch any empty strings from previous failures.
    $pdo->exec("ALTER TABLE `users` MODIFY `role` VARCHAR(20) NOT NULL;");
    $pdo->exec("UPDATE `users` SET `role` = 'customer' WHERE `role` NOT IN ('admin', 'customer');");

    // Perform the ALTER operations
    $pdo->exec("ALTER TABLE `users` 
        MODIFY `full_name` VARCHAR(150) NOT NULL,
        MODIFY `email` VARCHAR(255) NOT NULL,
        MODIFY `phone` VARCHAR(30) DEFAULT NULL,
        MODIFY `role` VARCHAR(20) NOT NULL DEFAULT 'customer',
        MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;");

    // Ensure CHECK constraint on role
    try { $pdo->exec("ALTER TABLE `users` DROP CONSTRAINT `chk_users_role`;"); } catch(Exception $e) {}
    $pdo->exec("ALTER TABLE `users` ADD CONSTRAINT `chk_users_role` CHECK (`role` IN ('customer', 'admin'));");

    // Rename unique email index if necessary
    $index_check = $pdo->query("SHOW INDEX FROM `users` WHERE Key_name = 'email'")->fetch();
    if ($index_check) {
        try { $pdo->exec("ALTER TABLE `users` RENAME INDEX `email` TO `uq_users_email`;"); } catch (Exception $e) {}
    }

    // Add role index if it doesn't exist
    $index_check = $pdo->query("SHOW INDEX FROM `users` WHERE Key_name = 'idx_users_role'")->fetch();
    if (!$index_check) {
        try { $pdo->exec("ALTER TABLE `users` ADD INDEX `idx_users_role` (`role`);"); } catch (Exception $e) {}
    }


    // ==========================================
    // 2. MIGRATE PHONES TABLE
    // ==========================================

    // First safely map the enum status values
    $pdo->exec("ALTER TABLE `phones` MODIFY `status` VARCHAR(20) NOT NULL;");
    $pdo->exec("UPDATE `phones` SET `status` = 'active' WHERE `status` IN ('available', 'out_of_stock');");
    $pdo->exec("UPDATE `phones` SET `status` = 'inactive' WHERE `status` = 'discontinued';");
    // Catch any empty/invalid ones
    $pdo->exec("UPDATE `phones` SET `status` = 'active' WHERE `status` NOT IN ('active', 'inactive');");

    // Perform the ALTER operations
    $pdo->exec("ALTER TABLE `phones`
        MODIFY `brand` VARCHAR(100) NOT NULL,
        MODIFY `model` VARCHAR(150) NOT NULL,
        MODIFY `price` DECIMAL(12,2) NOT NULL,
        MODIFY `stock_quantity` INT UNSIGNED NOT NULL DEFAULT 0,
        MODIFY `image` VARCHAR(500) DEFAULT NULL,
        MODIFY `is_latest` TINYINT(1) NOT NULL DEFAULT 0,
        MODIFY `status` VARCHAR(20) NOT NULL DEFAULT 'active';");
        
    // Add missing check constraints
    try { $pdo->exec("ALTER TABLE `phones` DROP CONSTRAINT `chk_phones_price`;"); } catch(Exception $e) {}
    $pdo->exec("ALTER TABLE `phones` ADD CONSTRAINT `chk_phones_price` CHECK (`price` >= 0);");
    
    try { $pdo->exec("ALTER TABLE `phones` DROP CONSTRAINT `chk_phones_status`;"); } catch(Exception $e) {}
    $pdo->exec("ALTER TABLE `phones` ADD CONSTRAINT `chk_phones_status` CHECK (`status` IN ('active', 'inactive'));");
    
    try { $pdo->exec("ALTER TABLE `phones` DROP CONSTRAINT `chk_phones_is_latest`;"); } catch(Exception $e) {}
    $pdo->exec("ALTER TABLE `phones` ADD CONSTRAINT `chk_phones_is_latest` CHECK (`is_latest` IN (0, 1));");

    // Add missing indexes
    $index_check = $pdo->query("SHOW INDEX FROM `phones` WHERE Key_name = 'idx_phones_model'")->fetch();
    if (!$index_check) {
        try { $pdo->exec("ALTER TABLE `phones` ADD INDEX `idx_phones_model` (`model`);"); } catch (Exception $e) {}
    }
    $index_check = $pdo->query("SHOW INDEX FROM `phones` WHERE Key_name = 'idx_phones_stock_quantity'")->fetch();
    if (!$index_check) {
        try { $pdo->exec("ALTER TABLE `phones` ADD INDEX `idx_phones_stock_quantity` (`stock_quantity`);"); } catch (Exception $e) {}
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    
    echo "Core migration successful.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
}
?>
