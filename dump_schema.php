<?php
require_once __DIR__ . '/config/database.php';

$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
    echo "TABLE: $table\n";
    $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
    echo $create['Create Table'] . "\n\n";
}
?>
