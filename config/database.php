<?php
// Include core functions
require_once __DIR__ . '/../includes/functions.php';

// Include auth and session handlers
require_once __DIR__ . '/../includes/auth.php';

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'phone_marketplace');

try {
    // Normal connection for the application
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // If database doesn't exist, this might fail, but it's fine for setup to override this
    die("Connection failed: " . $e->getMessage());
}
