<?php
require_once __DIR__ . '/config/database.php';

$stmt = $pdo->prepare("SELECT id, model FROM phones WHERE brand = 'Google'");
$stmt->execute();
$phones = $stmt->fetchAll();

foreach($phones as $p) {
    $vc = $pdo->query("SELECT COUNT(*) FROM phone_variants WHERE phone_id = " . $p['id'])->fetchColumn();
    echo $p['model'] . " variants: " . $vc . "\n";
}
