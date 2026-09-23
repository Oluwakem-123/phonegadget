<?php
// Setup script to initialize the database
// We manually connect without selecting the database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read the SQL file
    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    
    if ($sql === false) {
        die("Error: Could not read the schema.sql file.");
    }
    
    // Execute the SQL script
    $pdo->exec($sql);
    
    echo "<h1>Database Setup Successful!</h1>";
    echo "<p>The database 'phone_marketplace' and all tables have been created successfully.</p>";
    echo "<p><a href='index.php'>Go to Homepage</a></p>";
} catch (PDOException $e) {
    echo "<h1>Database Setup Failed</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
