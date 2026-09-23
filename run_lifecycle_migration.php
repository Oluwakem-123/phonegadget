<?php
require_once __DIR__ . '/config/database.php';

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // Check and add `status`
    $column_exists = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'status'")->fetch();
    if (!$column_exists) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'active' AFTER `role`;");
    }

    // Check and add `email_verified_at`
    $column_exists = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'email_verified_at'")->fetch();
    if (!$column_exists) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `email_verified_at` TIMESTAMP NULL DEFAULT NULL AFTER `status`;");
    }

    // Check and add `password_changed_at`
    $column_exists = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'password_changed_at'")->fetch();
    if (!$column_exists) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `password_changed_at` TIMESTAMP NULL DEFAULT NULL AFTER `email_verified_at`;");
    }

    // Check and add `last_login_at`
    $column_exists = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'last_login_at'")->fetch();
    if (!$column_exists) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `last_login_at` TIMESTAMP NULL DEFAULT NULL AFTER `password_changed_at`;");
    }

    // Check and add `deactivated_at`
    $column_exists = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'deactivated_at'")->fetch();
    if (!$column_exists) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `deactivated_at` TIMESTAMP NULL DEFAULT NULL AFTER `last_login_at`;");
    }

    // Check and add `deleted_at`
    $column_exists = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'deleted_at'")->fetch();
    if (!$column_exists) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `deleted_at` TIMESTAMP NULL DEFAULT NULL AFTER `deactivated_at`;");
    }

    // Check and add `updated_at`
    $column_exists = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'updated_at'")->fetch();
    if (!$column_exists) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;");
    }

    // Add CHECK constraint on status
    $check_exists = $pdo->query("SELECT * FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND CONSTRAINT_NAME = 'chk_users_status'")->fetch();
    if (!$check_exists) {
        $pdo->exec("ALTER TABLE `users` ADD CONSTRAINT `chk_users_status` CHECK (`status` IN ('active', 'suspended', 'deactivated'));");
    }

    // Add index on status
    $index_check = $pdo->query("SHOW INDEX FROM `users` WHERE Key_name = 'idx_users_status'")->fetch();
    if (!$index_check) {
        $pdo->exec("ALTER TABLE `users` ADD INDEX `idx_users_status` (`status`);");
    }

    // Add index on email_verified_at
    $index_check = $pdo->query("SHOW INDEX FROM `users` WHERE Key_name = 'idx_users_email_verified_at'")->fetch();
    if (!$index_check) {
        $pdo->exec("ALTER TABLE `users` ADD INDEX `idx_users_email_verified_at` (`email_verified_at`);");
    }

    // Add index on last_login_at
    $index_check = $pdo->query("SHOW INDEX FROM `users` WHERE Key_name = 'idx_users_last_login_at'")->fetch();
    if (!$index_check) {
        $pdo->exec("ALTER TABLE `users` ADD INDEX `idx_users_last_login_at` (`last_login_at`);");
    }

    // Add index on deleted_at
    $index_check = $pdo->query("SHOW INDEX FROM `users` WHERE Key_name = 'idx_users_deleted_at'")->fetch();
    if (!$index_check) {
        $pdo->exec("ALTER TABLE `users` ADD INDEX `idx_users_deleted_at` (`deleted_at`);");
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    echo "Lifecycle migration successful.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
}
?>
