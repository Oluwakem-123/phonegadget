<?php
require_once dirname(dirname(__DIR__)) . '/config/database.php';
requireAdmin($pdo);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . base_url('admin/orders/'));
    exit;
}

$csrf_token = $_POST['csrf_token'] ?? '';
$action = $_POST['action'] ?? '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$new_status = $_POST['status'] ?? '';
$redirect = $_SERVER['HTTP_REFERER'] ?? base_url('admin/orders/');

if (!verify_csrf_token($csrf_token)) {
    $_SESSION['flash_error'] = "Invalid security token.";
    header("Location: " . $redirect);
    exit;
}

if ($id <= 0) {
    $_SESSION['flash_error'] = "Invalid order ID.";
    header("Location: " . $redirect);
    exit;
}

$allowed_statuses = ['pending', 'processing', 'completed', 'cancelled'];

if ($action === 'update_status' && in_array($new_status, $allowed_statuses)) {
    try {
        $stmt = $pdo->prepare("SELECT user_id, status FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        
        if ($order) {
            $old_status = $order['status'];
            
            if ($old_status !== $new_status) {
                // Update the status
                $update_stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
                $update_stmt->execute([$new_status, $id]);
                
                // Create notification ONLY if status changed
                $title = "Order Status Update";
                $message = "Your order #{$id} status has been updated from {$old_status} to {$new_status}.";
                
                create_notification(
                    $pdo,
                    $order['user_id'],
                    'order',
                    $title,
                    $message,
                    null, // phone_id
                    $id   // order_id
                );
                
                $_SESSION['flash_success'] = "Order status updated to {$new_status} and customer notified.";
            } else {
                $_SESSION['flash_success'] = "Order status is already {$new_status}. No changes made.";
            }
        } else {
            $_SESSION['flash_error'] = "Order not found.";
        }
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = "Failed to update order status.";
    }
} else {
    $_SESSION['flash_error'] = "Invalid action or status.";
}

header("Location: " . $redirect);
exit;
