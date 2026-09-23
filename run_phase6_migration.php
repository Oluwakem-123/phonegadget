<?php
require_once __DIR__ . '/config/database.php';

echo "Starting Phase 6 database migration for Notifications...\n";

try {
    // We are migrating the `notifications` table safely.
    // First, check if there are any orphaned records that would break foreign keys.
    // If user_id doesn't exist in users, delete the notification to allow FK creation.
    echo "Cleaning up orphaned notifications if any...\n";
    $pdo->exec("DELETE FROM notifications WHERE user_id NOT IN (SELECT id FROM users)");
    
    // If phone_id doesn't exist in phones, set it to NULL.
    $pdo->exec("UPDATE notifications SET phone_id = NULL WHERE phone_id IS NOT NULL AND phone_id NOT IN (SELECT id FROM phones)");

    // Modify the table structure
    echo "Altering notifications table...\n";
    
    // Change engine to InnoDB if it isn't already, modify types and add new columns
    $sql = "
    ALTER TABLE `notifications`
        ENGINE=InnoDB,
        MODIFY COLUMN `user_id` BIGINT UNSIGNED NOT NULL,
        MODIFY COLUMN `phone_id` BIGINT UNSIGNED DEFAULT NULL,
        ADD COLUMN `order_id` BIGINT UNSIGNED DEFAULT NULL AFTER `phone_id`,
        ADD COLUMN `type` VARCHAR(50) NOT NULL DEFAULT 'system' AFTER `user_id`;
    ";
    
    $pdo->exec($sql);
    
    echo "Adding foreign key constraints...\n";
    
    // Helper function to drop FK if exists
    function add_foreign_key($pdo, $table, $fk_name, $column, $ref_table, $ref_column, $on_delete) {
        try {
            // Add constraint
            $pdo->exec("ALTER TABLE `$table` ADD CONSTRAINT `$fk_name` FOREIGN KEY (`$column`) REFERENCES `$ref_table` (`$ref_column`) ON DELETE $on_delete ON UPDATE CASCADE");
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate key') !== false || strpos($e->getMessage(), 'already exists') !== false) {
                // Ignore if it already exists
            } else {
                throw $e;
            }
        }
    }
    
    add_foreign_key($pdo, 'notifications', 'fk_notifications_user', 'user_id', 'users', 'id', 'CASCADE');
    add_foreign_key($pdo, 'notifications', 'fk_notifications_phone', 'phone_id', 'phones', 'id', 'SET NULL');
    add_foreign_key($pdo, 'notifications', 'fk_notifications_order', 'order_id', 'orders', 'id', 'CASCADE');
    
    echo "Migration completed successfully!\n";
    
} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
}
