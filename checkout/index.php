<?php
require_once dirname(__DIR__) . '/config/database.php';

// Checkout REQUIRES authentication
require_login();

$user_id = get_current_user_id();
$error = '';
$cart_items = [];
$cart_total = 0;

// Get customer info for pre-filling
try {
    $stmt = $pdo->prepare("SELECT full_name, phone FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_info = $stmt->fetch();
} catch (PDOException $e) {
    // Silently continue
    $user_info = ['full_name' => '', 'phone' => ''];
}

// Retrieve real cart from database
try {
    $stmt = $pdo->prepare("
        SELECT c.id as cart_id, c.quantity, p.id as phone_id, p.brand, p.model, p.price, p.stock_quantity, p.image, p.status 
        FROM cart_items c 
        JOIN phones p ON c.phone_id = p.id 
        WHERE c.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
    
    // Calculate total
    foreach ($cart_items as $item) {
        if ($item['status'] !== 'out_of_stock' && $item['status'] !== 'discontinued' && $item['stock_quantity'] > 0) {
            $cart_total += ($item['price'] * $item['quantity']);
        }
    }
} catch (PDOException $e) {
    $error = "Error loading checkout items.";
}

// If empty cart, they shouldn't be here (unless they are viewing error)
$is_empty = empty($cart_items);

// Process Checkout Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_empty) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!verify_csrf_token($csrf_token)) {
        $error = "Something went wrong. Please try again.";
    } else {
        $name = trim($_POST['customer_name'] ?? '');
        $phone = trim($_POST['customer_phone'] ?? '');
        $address = trim($_POST['delivery_address'] ?? '');
        $city = trim($_POST['delivery_city'] ?? '');
        $state = trim($_POST['delivery_state'] ?? '');
        
        if (empty($name)) $error = "Please enter your full name.";
        elseif (empty($phone)) $error = "Please enter your phone number.";
        elseif (empty($address)) $error = "Please enter your delivery address.";
        elseif (empty($city)) $error = "Please enter your city.";
        elseif (empty($state)) $error = "Please select your state.";
        else {
            try {
                $pdo->beginTransaction();
                
                // Re-fetch cart items to check stock within transaction using FOR UPDATE for row locking
                $stmt = $pdo->prepare("
                    SELECT c.quantity, p.id as phone_id, p.price, p.stock_quantity, p.status 
                    FROM cart_items c 
                    JOIN phones p ON c.phone_id = p.id 
                    WHERE c.user_id = ? FOR UPDATE
                ");
                $stmt->execute([$user_id]);
                $fresh_items = $stmt->fetchAll();
                
                if (empty($fresh_items)) {
                    throw new Exception("Your cart is empty.");
                }
                
                $final_total = 0;
                $order_items_data = [];
                
                // Validate stock and price
                foreach ($fresh_items as $f_item) {
                    if ($f_item['status'] === 'out_of_stock' || $f_item['status'] === 'discontinued') {
                        throw new Exception("Some items in your cart are no longer available.");
                    }
                    if ($f_item['stock_quantity'] < $f_item['quantity']) {
                        throw new Exception("Only {$f_item['stock_quantity']} units are currently available for some items.");
                    }
                    if ($f_item['stock_quantity'] <= 0) {
                        throw new Exception("Some items in your cart are no longer available in the requested quantity.");
                    }
                    
                    $subtotal = $f_item['price'] * $f_item['quantity'];
                    $final_total += $subtotal;
                    
                    $order_items_data[] = [
                        'phone_id' => $f_item['phone_id'],
                        'quantity' => $f_item['quantity'],
                        'unit_price' => $f_item['price'],
                        'subtotal' => $subtotal,
                        'current_stock' => $f_item['stock_quantity']
                    ];
                }
                
                // Create order
                $stmt = $pdo->prepare("
                    INSERT INTO orders (user_id, total_amount, status, customer_name, customer_phone, delivery_address, delivery_city, delivery_state) 
                    VALUES (?, ?, 'pending', ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$user_id, $final_total, $name, $phone, $address, $city, $state]);
                $order_id = $pdo->lastInsertId();
                
                // Create order items and reduce stock
                $stmt_insert_item = $pdo->prepare("INSERT INTO order_items (order_id, phone_id, quantity, unit_price, subtotal) VALUES (?, ?, ?, ?, ?)");
                $stmt_update_stock = $pdo->prepare("UPDATE phones SET stock_quantity = stock_quantity - ? WHERE id = ?");
                
                foreach ($order_items_data as $oi) {
                    $stmt_insert_item->execute([$order_id, $oi['phone_id'], $oi['quantity'], $oi['unit_price'], $oi['subtotal']]);
                    $stmt_update_stock->execute([$oi['quantity'], $oi['phone_id']]);
                }
                
                // Clear cart for this user
                $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
                $stmt->execute([$user_id]);
                
                // Create Notification
                require_once dirname(__DIR__) . '/includes/notifications.php';
                create_notification(
                    $pdo,
                    $user_id,
                    'Order placed successfully',
                    "Your order #$order_id has been placed successfully.",
                    'order',
                    null,
                    $order_id
                );
                
                $pdo->commit();
                
                // Redirect to success
                header("Location: " . base_url("orders/success.php?id=" . $order_id));
                exit;
                
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                // Expose business logic errors, hide technical ones
                $error = $e->getMessage();
                if (strpos($error, 'SQLSTATE') !== false) {
                    $error = "We couldn't place your order. Please try again.";
                }
            }
        }
    }
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Checkout</h1>
    </div>
    
    <?php if ($error): ?>
        <div class="bg-red-50 text-red-700 p-4 rounded-xl text-sm mb-8 border border-red-100 flex items-start gap-3">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="font-medium"><?php echo h($error); ?></span>
        </div>
    <?php endif; ?>
    
    <?php if ($is_empty): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center max-w-2xl mx-auto">
            <div class="w-20 h-20 bg-gray-50 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Your cart is empty</h2>
            <p class="text-gray-500 mb-8">You don't have any items in your cart to checkout.</p>
            <a href="<?php echo base_url('phones/'); ?>" class="inline-block bg-primary text-white font-semibold px-8 py-3.5 rounded-xl shadow hover:bg-gray-800 transition">Continue Shopping</a>
        </div>
    <?php else: ?>
    
        <form method="POST" action="" class="flex flex-col lg:flex-row gap-8" id="checkout-form" data-custom-submit="true">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            
            <!-- Left Column: Delivery Form -->
            <div class="w-full lg:w-2/3 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Delivery Information
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                            <input type="text" name="customer_name" required value="<?php echo h($_POST['customer_name'] ?? $user_info['full_name']); ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary text-sm">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                            <input type="tel" name="customer_phone" required value="<?php echo h($_POST['customer_phone'] ?? $user_info['phone']); ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary text-sm">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Address *</label>
                            <textarea name="delivery_address" required rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary text-sm"><?php echo h($_POST['delivery_address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">City *</label>
                            <input type="text" name="delivery_city" required value="<?php echo h($_POST['delivery_city'] ?? ''); ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">State *</label>
                            <select name="delivery_state" required class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary text-sm bg-white">
                                <option value="">Select a state...</option>
                                <?php 
                                $states = ['Lagos', 'Abuja', 'Kano', 'Rivers', 'Oyo', 'Enugu', 'Ogun', 'Kaduna'];
                                $selected = $_POST['delivery_state'] ?? '';
                                foreach ($states as $st) {
                                    $sel = ($st === $selected) ? 'selected' : '';
                                    echo "<option value=\"$st\" $sel>$st</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column: Order Summary -->
            <div class="w-full lg:w-1/3">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 sticky top-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Order Summary</h2>
                    
                    <div class="space-y-4 max-h-[400px] overflow-y-auto mb-6 pr-2">
                        <?php foreach ($cart_items as $item): 
                            $is_available = !($item['status'] === 'out_of_stock' || $item['status'] === 'discontinued' || $item['stock_quantity'] <= 0);
                        ?>
                            <div class="flex gap-4 items-start <?php echo !$is_available ? 'opacity-50' : ''; ?>">
                                <div class="w-16 h-16 bg-gray-50 rounded-lg flex items-center justify-center p-2 flex-shrink-0 border border-gray-100">
                                    <?php if(!empty($item['image'])): ?>
                                        <img src="<?php echo base_url('assets/images/' . h($item['image'])); ?>" alt="Phone" class="max-w-full max-h-full object-contain">
                                    <?php else: ?>
                                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow">
                                    <div class="text-xs font-medium text-gray-500 uppercase"><?php echo h($item['brand']); ?></div>
                                    <h4 class="text-sm font-bold text-gray-900 leading-tight"><?php echo h($item['model']); ?></h4>
                                    
                                    <?php if ($is_available): ?>
                                        <div class="flex justify-between items-center mt-1">
                                            <span class="text-xs text-gray-500">Qty: <?php echo $item['quantity']; ?></span>
                                            <span class="text-sm font-bold text-gray-900">₦<?php echo number_format($item['price'] * $item['quantity']); ?></span>
                                        </div>
                                        <?php if ($item['stock_quantity'] < $item['quantity']): ?>
                                            <p class="text-xs text-red-500 mt-1">Only <?php echo $item['stock_quantity']; ?> available</p>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="text-xs text-red-500 font-medium mt-1">Unavailable</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="border-t border-gray-100 pt-4 mb-6">
                        <div class="flex justify-between items-center">
                            <span class="text-base font-bold text-gray-900">Total</span>
                            <span class="text-2xl font-extrabold text-gray-900">₦<?php echo number_format($cart_total); ?></span>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-secondary text-white font-bold py-3.5 px-6 rounded-xl shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition flex items-center justify-center gap-2" onclick="const btn = this; setTimeout(() => { btn.disabled = true; btn.innerHTML = 'Processing...'; btn.form.submit(); }, 10);">
                        Place Order
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                    
                    <div class="mt-4 text-center">
                        <a href="<?php echo base_url('cart/'); ?>" class="text-sm text-gray-500 hover:text-gray-800 font-medium">Return to Cart</a>
                    </div>
                </div>
            </div>
            
        </form>
    
    <?php endif; ?>

</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
