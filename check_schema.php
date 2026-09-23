<?php
require_once __DIR__ . '/config/database.php';

echo "TABLES:\n";
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($tables as $table) {
    echo "- $table\n";
    $columns = $pdo->query("DESCRIBE `$table`")->fetchAll();
    foreach ($columns as $col) {
        echo "  * " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
}
