<?php
require_once __DIR__ . '/config/database.php';

try {
    // Check if an admin exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    if ($stmt->fetchColumn() == 0) {
        // No admin, let's promote user ID 1 or create one
        $stmt = $pdo->query("SELECT id FROM users LIMIT 1");
        $user = $stmt->fetch();
        if ($user) {
            $pdo->exec("UPDATE users SET role = 'admin' WHERE id = " . $user['id']);
            echo "Promoted user ID {$user['id']} to admin.\n";
        } else {
            echo "No users in database. Create a user normally first, then run this again.\n";
        }
    } else {
        echo "Admin already exists.\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
