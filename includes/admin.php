<?php
/**
 * Admin Security & Authorization Helper
 */

/**
 * Check if the currently logged in user is an active admin.
 * Does NOT redirect.
 * 
 * @param PDO $pdo
 * @return bool
 */
function isAdmin($pdo) {
    if (!is_logged_in()) {
        return false;
    }
    
    $user_id = get_current_user_id();
    
    try {
        $stmt = $pdo->prepare("SELECT role, status, deleted_at FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        
        if ($user) {
            // Must be admin, must be active, must not be soft deleted
            if ($user['role'] === 'admin' && $user['status'] === 'active' && $user['deleted_at'] === null) {
                return true;
            }
        }
    } catch (PDOException $e) {
        // Log error if needed, default to false
    }
    
    return false;
}

/**
 * Require the current user to be an active admin.
 * Redirects to home page if unauthorized.
 * 
 * @param PDO $pdo
 */
function requireAdmin($pdo) {
    if (!isAdmin($pdo)) {
        header("Location: " . base_url());
        exit;
    }
}
