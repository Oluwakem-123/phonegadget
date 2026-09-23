<?php
require_once dirname(dirname(__DIR__)) . '/config/database.php';
requireAdmin($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . base_url('admin/phones/'));
    exit;
}

$csrf_token = $_POST['csrf_token'] ?? '';
$action = $_POST['action'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$redirect = $_SERVER['HTTP_REFERER'] ?? base_url('admin/phones/');

if (!verify_csrf_token($csrf_token)) {
    $_SESSION['flash_error'] = "Invalid security token.";
    header("Location: " . $redirect);
    exit;
}

if ($id <= 0) {
    $_SESSION['flash_error'] = "Invalid phone ID.";
    header("Location: " . $redirect);
    exit;
}

if ($action === 'toggle_status') {
    try {
        $stmt = $pdo->prepare("SELECT status FROM phones WHERE id = ?");
        $stmt->execute([$id]);
        $phone = $stmt->fetch();
        
        if ($phone) {
            $new_status = $phone['status'] === 'active' ? 'inactive' : 'active';
            $update_stmt = $pdo->prepare("UPDATE phones SET status = ? WHERE id = ?");
            $update_stmt->execute([$new_status, $id]);
            
            $_SESSION['flash_success'] = "Phone status updated to {$new_status}.";
        } else {
            $_SESSION['flash_error'] = "Phone not found.";
        }
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = "Failed to update phone status.";
    }
}

header("Location: " . $redirect);
exit;
