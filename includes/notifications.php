<?php
/**
 * Notifications Helper Functions
 */

/**
 * Create a new notification for a user
 * 
 * @param PDO $pdo The PDO connection
 * @param int $user_id The recipient's user ID
 * @param string $title Notification title
 * @param string $message Notification message body
 * @param string $type The notification type (system, order, order_status, account, product)
 * @param int|null $phone_id Optional related phone ID
 * @param int|null $order_id Optional related order ID
 * @return bool True on success
 */
function create_notification($pdo, $user_id, $title, $message, $type = 'system', $phone_id = null, $order_id = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, title, message, type, phone_id, order_id, is_read, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, 0, CURRENT_TIMESTAMP)
        ");
        return $stmt->execute([$user_id, $title, $message, $type, $phone_id, $order_id]);
    } catch (PDOException $e) {
        // Log error internally if logging system exists
        error_log("Failed to create notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Get unread notification count for a user
 * 
 * @param PDO $pdo The PDO connection
 * @param int $user_id The user ID
 * @return int Number of unread notifications
 */
function get_unread_notification_count($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch();
        return $row ? (int)$row['count'] : 0;
    } catch (PDOException $e) {
        return 0;
    }
}

/**
 * Get paginated notifications for a user
 * 
 * @param PDO $pdo The PDO connection
 * @param int $user_id The user ID
 * @param int $page Current page number
 * @param int $limit Number of items per page
 * @return array ['items' => array, 'total' => int, 'pages' => int]
 */
function get_user_notifications($pdo, $user_id, $page = 1, $limit = 20) {
    $page = max(1, (int)$page);
    $limit = max(1, min(100, (int)$limit));
    $offset = ($page - 1) * $limit;
    
    try {
        // Get total count
        $count_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM notifications WHERE user_id = ?");
        $count_stmt->execute([$user_id]);
        $total = (int)$count_stmt->fetch()['total'];
        
        $pages = ceil($total / $limit);
        
        // Get items
        $stmt = $pdo->prepare("
            SELECT id, title, message, type, phone_id, order_id, is_read, created_at 
            FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        
        // PDO requires integers for LIMIT/OFFSET when using emulate prepares
        $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        $items = $stmt->fetchAll();
        
        return [
            'items' => $items,
            'total' => $total,
            'pages' => $pages
        ];
    } catch (PDOException $e) {
        return ['items' => [], 'total' => 0, 'pages' => 0];
    }
}

/**
 * Mark a single notification as read securely checking ownership
 * 
 * @param PDO $pdo
 * @param int $user_id
 * @param int $notification_id
 * @return bool
 */
function mark_notification_as_read($pdo, $user_id, $notification_id) {
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ? AND is_read = 0");
        return $stmt->execute([$notification_id, $user_id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Mark all notifications as read for a user
 * 
 * @param PDO $pdo
 * @param int $user_id
 * @return bool
 */
function mark_all_notifications_as_read($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        return $stmt->execute([$user_id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Delete a specific notification checking ownership
 * 
 * @param PDO $pdo
 * @param int $user_id
 * @param int $notification_id
 * @return bool
 */
function delete_notification($pdo, $user_id, $notification_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
        return $stmt->execute([$notification_id, $user_id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Delete all notifications for a user
 * 
 * @param PDO $pdo
 * @param int $user_id
 * @return bool
 */
function delete_all_notifications($pdo, $user_id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
        return $stmt->execute([$user_id]);
    } catch (PDOException $e) {
        return false;
    }
}

/**
 * Helper to display relative time for notifications
 * 
 * @param string $datetime
 * @return string
 */
function format_notification_time($datetime) {
    if (empty($datetime)) return '';
    
    $time = strtotime($datetime);
    if (!$time) return '';
    
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return $mins . ' min' . ($mins > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 172800) {
        return 'Yesterday at ' . date('g:i A', $time);
    } else {
        return date('M j, Y \a\t g:i A', $time);
    }
}
