<?php
// Start the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize guest cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/**
 * Check if the current user is logged in
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user ID if logged in
 * @return int|null
 */
function get_current_user_id() {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

/**
 * Redirect non-logged in users to the login page
 */
function require_login() {
    if (!is_logged_in()) {
        $_SESSION['flash_error'] = "Please log in to access this feature.";
        // Save intended destination
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header("Location: " . base_url('auth/login.php'));
        exit;
    }
    
    // Check if account is still active in database
    global $pdo;
    if (isset($pdo)) {
        try {
            $stmt = $pdo->prepare("SELECT status, deleted_at FROM users WHERE id = ?");
            $stmt->execute([get_current_user_id()]);
            $user = $stmt->fetch();
            
            if (!$user || $user['status'] !== 'active' || $user['deleted_at'] !== null) {
                // Account is no longer active, destroy session
                session_unset();
                session_destroy();
                session_start();
                $_SESSION['flash_error'] = "Your session has expired or your account has been deactivated.";
                header("Location: " . base_url('auth/login.php'));
                exit;
            }
        } catch (PDOException $e) {
            // If DB fails, we allow the session to continue temporarily rather than logging them out
        }
    }
}

/**
 * Get total number of items in the cart (sum of quantities)
 * @return int
 */
function get_cart_count() {
    $count = 0;
    
    if (is_logged_in()) {
        global $pdo;
        if (isset($pdo)) {
            try {
                $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?");
                $stmt->execute([get_current_user_id()]);
                $result = $stmt->fetch();
                return $result['total'] ? (int)$result['total'] : 0;
            } catch (PDOException $e) {
                // Fallback on error
                return 0;
            }
        }
    } else {
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $item) {
                $count += (int)$item['quantity'];
            }
        }
    }
    return $count;
}

/**
 * Merge guest cart into user cart upon login
 * @param PDO $pdo
 * @param int $user_id
 */
function merge_guest_cart($pdo, $user_id) {
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $phone_id => $item) {
            $qty = (int)$item['quantity'];
            
            try {
                // Check if phone exists and get stock
                $stmt = $pdo->prepare("SELECT stock_quantity, status FROM phones WHERE id = ?");
                $stmt->execute([$phone_id]);
                $phone = $stmt->fetch();
                
                if ($phone && $phone['status'] !== 'discontinued') {
                    // Check if already in user's cart
                    $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND phone_id = ?");
                    $stmt->execute([$user_id, $phone_id]);
                    $existing = $stmt->fetch();
                    
                    if ($existing) {
                        $new_qty = $existing['quantity'] + $qty;
                        if ($new_qty > $phone['stock_quantity']) {
                            $new_qty = $phone['stock_quantity'];
                        }
                        $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE id = ?");
                        $stmt->execute([$new_qty, $existing['id']]);
                    } else {
                        if ($qty > $phone['stock_quantity']) {
                            $qty = $phone['stock_quantity'];
                        }
                        if ($qty > 0) {
                            $stmt = $pdo->prepare("INSERT INTO cart_items (user_id, phone_id, quantity) VALUES (?, ?, ?)");
                            $stmt->execute([$user_id, $phone_id, $qty]);
                        }
                    }
                }
            } catch (PDOException $e) {
                // Silently skip if error occurs during merge
                continue;
            }
        }
        // Clear guest cart
        $_SESSION['cart'] = [];
    }
}

/**
 * Generate a CSRF token for forms
 * @return string
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify a CSRF token
 * @param string $token
 * @return bool
 */
function verify_csrf_token($token) {
    if (!empty($_SESSION['csrf_token']) && !empty($token) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}
