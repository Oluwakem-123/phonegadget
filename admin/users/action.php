<?php
require_once dirname(dirname(__DIR__)) . '/config/database.php';
requireAdmin($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . base_url('admin/users/'));
    exit;
}

$csrf_token = $_POST['csrf_token'] ?? '';
$action = $_POST['action'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$redirect = $_SERVER['HTTP_REFERER'] ?? base_url('admin/users/');

if (!verify_csrf_token($csrf_token)) {
    $_SESSION['flash_error'] = "Invalid security token.";
    header("Location: " . $redirect);
    exit;
}

if ($id <= 0) {
    $_SESSION['flash_error'] = "Invalid user ID.";
    header("Location: " . $redirect);
    exit;
}

// Cannot modify self through this interface to prevent accidental admin lockout
if ($id === get_current_user_id()) {
    $_SESSION['flash_error'] = "You cannot modify your own account from here.";
    header("Location: " . $redirect);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, status, deleted_at, role FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['flash_error'] = "User not found.";
        header("Location: " . $redirect);
        exit;
    }
    
    // Do not modify other admins to prevent hostile takeovers or accidental lockouts, 
    // unless building a full super-admin role system
    if ($user['role'] === 'admin') {
        $_SESSION['flash_error'] = "You cannot modify another administrator.";
        header("Location: " . $redirect);
        exit;
    }

    if ($user['deleted_at'] !== null) {
        $_SESSION['flash_error'] = "This account is permanently soft-deleted and cannot be modified.";
        header("Location: " . $redirect);
        exit;
    }

    if ($action === 'suspend' && $user['status'] === 'active') {
        $update_stmt = $pdo->prepare("UPDATE users SET status = 'suspended' WHERE id = ?");
        $update_stmt->execute([$id]);
        $_SESSION['flash_success'] = "Customer has been suspended.";
    } 
    elseif ($action === 'deactivate' && $user['status'] === 'active') {
        $update_stmt = $pdo->prepare("UPDATE users SET status = 'deactivated', deactivated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $update_stmt->execute([$id]);
        $_SESSION['flash_success'] = "Customer has been deactivated.";
    }
    elseif ($action === 'restore' && in_array($user['status'], ['suspended', 'deactivated'])) {
        $update_stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $update_stmt->execute([$id]);
        $_SESSION['flash_success'] = "Customer has been restored and is now active.";
    } 
    else {
        $_SESSION['flash_error'] = "Invalid action or the user is not in the correct state for this action.";
    }

} catch (PDOException $e) {
    $_SESSION['flash_error'] = "Failed to update user status.";
}

header("Location: " . $redirect);
exit;
