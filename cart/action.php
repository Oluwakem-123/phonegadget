<?php
require_once dirname(__DIR__) . '/config/database.php';

// Accept only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . base_url('cart/'));
    exit;
}

$action = $_POST['action'] ?? 'add';
$phone_id = isset($_POST['phone_id']) && is_numeric($_POST['phone_id']) ? (int)$_POST['phone_id'] : 0;
$variant_id = isset($_POST['variant_id']) && is_numeric($_POST['variant_id']) ? (int)$_POST['variant_id'] : null;
$quantity = isset($_POST['quantity']) && is_numeric($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($phone_id <= 0 && $action !== 'clear') {
    $_SESSION['flash_error'] = "Invalid product.";
    header("Location: " . base_url('cart/'));
    exit;
}

try {
    $is_logged_in = is_logged_in();
    $user_id = get_current_user_id();

    if ($action === 'clear') {
        if ($is_logged_in) {
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $stmt->execute([$user_id]);
        } else {
            $_SESSION['cart'] = [];
        }
        $_SESSION['flash_success'] = "Your cart has been cleared.";
        header("Location: " . base_url('cart/'));
        exit;
    }

    // Check product exists and get stock
    $stmt = $pdo->prepare("SELECT id, model, stock_quantity, status FROM phones WHERE id = ?");
    $stmt->execute([$phone_id]);
    $phone = $stmt->fetch();

    $variant = null;
    $stock_to_check = $phone ? $phone['stock_quantity'] : 0;
    if ($phone && $variant_id) {
        $v_stmt = $pdo->prepare("SELECT id, stock_quantity FROM phone_variants WHERE id = ? AND phone_id = ?");
        $v_stmt->execute([$variant_id, $phone_id]);
        $variant = $v_stmt->fetch();
        if ($variant) {
            $stock_to_check = $variant['stock_quantity'];
        } else {
            $variant_id = null; // Invalid variant for this phone
        }
    }

    if (!$phone) {
        $_SESSION['flash_error'] = "Sorry, this phone could not be found.";
    } elseif ($phone['status'] === 'out_of_stock' || $phone['status'] === 'discontinued' || $stock_to_check <= 0) {
        $_SESSION['flash_error'] = "This phone option is currently unavailable.";
    } else {
        
        // Helper to get current quantity
        $current_qty = 0;
        $cart_key = $phone_id . ($variant_id ? '-' . $variant_id : '');

        if ($is_logged_in) {
            if ($variant_id) {
                $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND phone_id = ? AND variant_id = ?");
                $stmt->execute([$user_id, $phone_id, $variant_id]);
            } else {
                $stmt = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE user_id = ? AND phone_id = ? AND variant_id IS NULL");
                $stmt->execute([$user_id, $phone_id]);
            }
            $existing_item = $stmt->fetch();
            if ($existing_item) $current_qty = (int)$existing_item['quantity'];
        } else {
            if (isset($_SESSION['cart'][$cart_key])) {
                $current_qty = (int)$_SESSION['cart'][$cart_key]['quantity'];
            }
        }

        // Handle different actions
        switch ($action) {
            case 'add':
                $new_qty = $current_qty + $quantity;
                
                if ($new_qty > $stock_to_check) {
                    $_SESSION['flash_warning'] = "Only " . $stock_to_check . " units are currently available.";
                    $new_qty = $stock_to_check; // maximize to stock
                } else {
                    if ($current_qty > 0) {
                        $_SESSION['flash_success'] = "Quantity updated in cart.";
                    } else {
                        $_SESSION['flash_success'] = "Phone added to cart.";
                    }
                }
                
                if ($new_qty > 0) {
                    if ($is_logged_in) {
                        if ($current_qty > 0) {
                            if ($variant_id) {
                                $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND phone_id = ? AND variant_id = ?");
                                $stmt->execute([$new_qty, $user_id, $phone_id, $variant_id]);
                            } else {
                                $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND phone_id = ? AND variant_id IS NULL");
                                $stmt->execute([$new_qty, $user_id, $phone_id]);
                            }
                        } else {
                            $stmt = $pdo->prepare("INSERT INTO cart_items (user_id, phone_id, variant_id, quantity) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$user_id, $phone_id, $variant_id, $new_qty]);
                        }
                    } else {
                        $_SESSION['cart'][$cart_key] = [
                            'phone_id' => $phone_id,
                            'variant_id' => $variant_id,
                            'quantity' => $new_qty
                        ];
                    }
                }
                break;
                
            case 'update':
                if ($quantity <= 0) {
                    if ($is_logged_in) {
                        if ($variant_id) {
                            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ? AND phone_id = ? AND variant_id = ?");
                            $stmt->execute([$user_id, $phone_id, $variant_id]);
                        } else {
                            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ? AND phone_id = ? AND variant_id IS NULL");
                            $stmt->execute([$user_id, $phone_id]);
                        }
                    } else {
                        unset($_SESSION['cart'][$cart_key]);
                    }
                    $_SESSION['flash_success'] = "Phone removed from cart.";
                } else {
                    $new_qty = $quantity;
                    if ($new_qty > $stock_to_check) {
                        $_SESSION['flash_warning'] = "Only " . $stock_to_check . " units are currently available.";
                        $new_qty = $stock_to_check;
                    } else {
                        $_SESSION['flash_success'] = "Quantity updated in cart.";
                    }
                    
                    if ($is_logged_in) {
                        if ($current_qty > 0) {
                            if ($variant_id) {
                                $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND phone_id = ? AND variant_id = ?");
                                $stmt->execute([$new_qty, $user_id, $phone_id, $variant_id]);
                            } else {
                                $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND phone_id = ? AND variant_id IS NULL");
                                $stmt->execute([$new_qty, $user_id, $phone_id]);
                            }
                        } else {
                            $stmt = $pdo->prepare("INSERT INTO cart_items (user_id, phone_id, variant_id, quantity) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$user_id, $phone_id, $variant_id, $new_qty]);
                        }
                    } else {
                        $_SESSION['cart'][$cart_key] = [
                            'phone_id' => $phone_id,
                            'variant_id' => $variant_id,
                            'quantity' => $new_qty
                        ];
                    }
                }
                break;
                
            case 'remove':
                if ($is_logged_in) {
                    if ($variant_id) {
                        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ? AND phone_id = ? AND variant_id = ?");
                        $stmt->execute([$user_id, $phone_id, $variant_id]);
                    } else {
                        $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ? AND phone_id = ? AND variant_id IS NULL");
                        $stmt->execute([$user_id, $phone_id]);
                    }
                } else {
                    unset($_SESSION['cart'][$cart_key]);
                }
                $_SESSION['flash_success'] = "Phone removed from cart.";
                break;
        }
    }
} catch (PDOException $e) {
    if ($action === 'clear') {
        $_SESSION['flash_error'] = "We couldn't clear your cart. Please try again.";
    } else {
        $_SESSION['flash_error'] = "We couldn't add this phone to your cart. Please try again. " . $e->getMessage();
    }
}

// Redirect back to where they came from (or cart)
$redirect = $_SERVER['HTTP_REFERER'] ?? base_url('cart/');
// Fallback if referer is action.php itself
if (strpos($redirect, 'action.php') !== false) {
    $redirect = base_url('cart/');
}
header("Location: " . $redirect);
exit;
