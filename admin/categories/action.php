<?php
require_once dirname(dirname(__DIR__)) . '/config/database.php';
requireAdmin($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . base_url('admin/categories/'));
    exit;
}

$csrf_token = $_POST['csrf_token'] ?? '';
$action = $_POST['action'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$redirect = $_SERVER['HTTP_REFERER'] ?? base_url('admin/categories/');

if (!verify_csrf_token($csrf_token)) {
    $_SESSION['flash_error'] = "Invalid security token.";
    header("Location: " . $redirect);
    exit;
}

if ($id <= 0) {
    $_SESSION['flash_error'] = "Invalid category ID.";
    header("Location: " . $redirect);
    exit;
}

if ($action === 'delete') {
    try {
        // Check if category has phones
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM phones WHERE category_id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $_SESSION['flash_error'] = "Cannot delete this category because it contains phones.";
        } else {
            $delete_stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $delete_stmt->execute([$id]);
            $_SESSION['flash_success'] = "Category deleted successfully.";
        }
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = "Failed to delete category.";
    }
}

header("Location: " . $redirect);
exit;
