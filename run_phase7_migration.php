<?php
require_once __DIR__ . '/config/database.php';

echo "Starting Phase 7 database migration for Favorites...\n";

try {
    echo "Cleaning up orphaned favorites if any...\n";
    $pdo->exec("DELETE FROM favorites WHERE user_id NOT IN (SELECT id FROM users)");
    $pdo->exec("DELETE FROM favorites WHERE phone_id NOT IN (SELECT id FROM phones)");

    echo "Altering favorites table...\n";
    
    // Change engine to InnoDB, modify types
    $sql = "
    ALTER TABLE `favorites`
        ENGINE=InnoDB,
        MODIFY COLUMN `user_id` BIGINT UNSIGNED NOT NULL,
        MODIFY COLUMN `phone_id` BIGINT UNSIGNED NOT NULL;
    ";
    
    $pdo->exec($sql);
    
    echo "Adding foreign key constraints...\n";
    
    function add_foreign_key($pdo, $table, $fk_name, $column, $ref_table, $ref_column, $on_delete) {
        try {
            $pdo->exec("ALTER TABLE `$table` ADD CONSTRAINT `$fk_name` FOREIGN KEY (`$column`) REFERENCES `$ref_table` (`$ref_column`) ON DELETE $on_delete ON UPDATE CASCADE");
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate key') !== false || strpos($e->getMessage(), 'already exists') !== false) {
                // Ignore if it already exists
            } else {
                throw $e;
            }
        }
    }
    
    add_foreign_key($pdo, 'favorites', 'fk_favorites_user', 'user_id', 'users', 'id', 'CASCADE');
    add_foreign_key($pdo, 'favorites', 'fk_favorites_phone', 'phone_id', 'phones', 'id', 'CASCADE');
    
    echo "Migration completed successfully!\n";
    
} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage() . "\n");
}
