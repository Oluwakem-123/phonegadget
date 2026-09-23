<?php
require_once dirname(__DIR__) . '/config/database.php';

// Must be logged in
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Only accept POST requests
    header("Location: " . base_url());
    exit;
}

$user_id = get_current_user_id();
$csrf_token = $_POST['csrf_token'] ?? '';
$action = $_POST['action'] ?? '';
$phone_id = isset($_POST['phone_id']) ? (int)$_POST['phone_id'] : 0;
$redirect = $_SERVER['HTTP_REFERER'] ?? base_url();

if (!verify_csrf_token($csrf_token)) {
    $_SESSION['flash_error'] = 'Invalid security token. Please try again.';
    header("Location: " . $redirect);
    exit;
}

switch ($action) {
    case 'add':
        if ($phone_id > 0) {
            if (add_favorite($pdo, $user_id, $phone_id)) {
                $_SESSION['flash_success'] = 'Added to favorites.';
            } else {
                $_SESSION['flash_error'] = "We couldn't add this phone to your favorites. Please try again.";
            }
        }
        break;
        
    case 'remove':
        if ($phone_id > 0) {
            if (remove_favorite($pdo, $user_id, $phone_id)) {
                $_SESSION['flash_success'] = 'Removed from favorites.';
            } else {
                $_SESSION['flash_error'] = "We couldn't remove this phone from your favorites. Please try again.";
            }
        }
        break;
        
    case 'remove_all':
        if (remove_all_favorites($pdo, $user_id)) {
            $_SESSION['flash_success'] = 'All favorites have been removed.';
        } else {
            $_SESSION['flash_error'] = "We couldn't remove your favorites. Please try again.";
        }
        $redirect = base_url('account/favorites.php');
        break;
        
    case 'toggle': // Fallback for the phone card form which doesn't specify add/remove explicitly
        if ($phone_id > 0) {
            if (is_favorite($pdo, $user_id, $phone_id)) {
                if (remove_favorite($pdo, $user_id, $phone_id)) {
                    $_SESSION['flash_success'] = 'Removed from favorites.';
                } else {
                    $_SESSION['flash_error'] = "We couldn't remove this phone from your favorites. Please try again.";
                }
            } else {
                if (add_favorite($pdo, $user_id, $phone_id)) {
                    $_SESSION['flash_success'] = 'Added to favorites.';
                } else {
                    $_SESSION['flash_error'] = "We couldn't add this phone to your favorites. Please try again.";
                }
            }
        }
        break;
}

header("Location: " . $redirect);
exit;
