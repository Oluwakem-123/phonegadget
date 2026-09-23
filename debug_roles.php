<?php
require 'config/database.php';
$roles = $pdo->query('SELECT DISTINCT role FROM users')->fetchAll(PDO::FETCH_ASSOC);
print_r($roles);
