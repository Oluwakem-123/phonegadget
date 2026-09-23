<?php
require 'config/database.php';
$statuses = $pdo->query('SELECT DISTINCT status FROM phones')->fetchAll(PDO::FETCH_ASSOC);
print_r($statuses);
