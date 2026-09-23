<?php
require_once dirname(__DIR__) . '/config/database.php';

// Fetch cart items from DB to get current prices and details
$cart_items = [];
$cart_total = 0;
$out_of_stock_warnings = false;

if (is_logged_in()) {
    $user_id = get_current_user_id();
    try {
        $stmt = $pdo->prepare("
            SELECT ci.quantity, ci.variant_id, p.id, p.brand, p.model, p.price as base_price, p.image, p.stock_quantity as base_stock, p.status,
                   v.price as variant_price, v.stock_quantity as variant_stock, v.storage, v.color
            FROM cart_items ci
            JOIN phones p ON ci.phone_id = p.id
            LEFT JOIN phone_variants v ON ci.variant_id = v.id
            WHERE ci.user_id = ?
        ");
        $stmt->execute([$user_id]);
        $db_items = $stmt->fetchAll();
        
        foreach ($db_items as $phone) {
            $id = $phone['id'];
            $qty = (int)$phone['quantity'];
            $variant_id = $phone['variant_id'];
            
            $price = $variant_id ? $phone['variant_price'] : $phone['base_price'];
            $stock = $variant_id ? $phone['variant_stock'] : $phone['base_stock'];
            
            $is_unavailable = ($phone['status'] === 'out_of_stock' || $phone['status'] === 'discontinued' || $stock <= 0);
            if ($is_unavailable) {
                $out_of_stock_warnings = true;
            } elseif ($qty > $stock) {
                $qty = $stock;
                if ($variant_id) {
                    $upd_stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND phone_id = ? AND variant_id = ?");
                    $upd_stmt->execute([$qty, $user_id, $id, $variant_id]);
                } else {
                    $upd_stmt = $pdo->prepare("UPDATE cart_items SET quantity = ? WHERE user_id = ? AND phone_id = ? AND variant_id IS NULL");
                    $upd_stmt->execute([$qty, $user_id, $id]);
                }
                $out_of_stock_warnings = true;
            }
            
            $subtotal = $price * $qty;
            if (!$is_unavailable) {
                $cart_total += $subtotal;
            }
            
            $cart_items[] = [
                'phone' => $phone,
                'quantity' => $qty,
                'subtotal' => $subtotal,
                'price' => $price,
                'stock' => $stock,
                'is_unavailable' => $is_unavailable
            ];
        }
    } catch (PDOException $e) {
        $_SESSION['flash_error'] = "Error loading cart.";
    }
} else {
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $cart_key => $item) {
            $phone_id = $item['phone_id'];
            $variant_id = $item['variant_id'];
            $qty = $item['quantity'];
            
            try {
                $stmt = $pdo->prepare("SELECT id, brand, model, price as base_price, image, stock_quantity as base_stock, status FROM phones WHERE id = ?");
                $stmt->execute([$phone_id]);
                $phone = $stmt->fetch();
                
                if ($phone) {
                    $phone['variant_id'] = $variant_id;
                    $phone['variant_price'] = null;
                    $phone['variant_stock'] = null;
                    $phone['storage'] = null;
                    $phone['color'] = null;
                    
                    if ($variant_id) {
                        $v_stmt = $pdo->prepare("SELECT price, stock_quantity, storage, color FROM phone_variants WHERE id = ? AND phone_id = ?");
                        $v_stmt->execute([$variant_id, $phone_id]);
                        $variant = $v_stmt->fetch();
                        if ($variant) {
                            $phone['variant_price'] = $variant['price'];
                            $phone['variant_stock'] = $variant['stock_quantity'];
                            $phone['storage'] = $variant['storage'];
                            $phone['color'] = $variant['color'];
                        }
                    }
                    
                    $price = $variant_id && $phone['variant_price'] !== null ? $phone['variant_price'] : $phone['base_price'];
                    $stock = $variant_id && $phone['variant_stock'] !== null ? $phone['variant_stock'] : $phone['base_stock'];
                    
                    // Check if stock changed since adding
                    $is_unavailable = ($phone['status'] === 'out_of_stock' || $phone['status'] === 'discontinued' || $stock <= 0);
                    if ($is_unavailable) {
                        $out_of_stock_warnings = true;
                    } elseif ($qty > $stock) {
                        // Adjust to max available if stock dropped
                        $qty = $stock;
                        $_SESSION['cart'][$cart_key]['quantity'] = $qty;
                        $out_of_stock_warnings = true;
                    }
                    
                    $subtotal = $price * $qty;
                    if (!$is_unavailable) {
                        $cart_total += $subtotal;
                    }
                    
                    $cart_items[] = [
                        'phone' => $phone,
                        'quantity' => $qty,
                        'subtotal' => $subtotal,
                        'price' => $price,
                        'stock' => $stock,
                        'is_unavailable' => $is_unavailable
                    ];
                }
            } catch (PDOException $e) {
                $_SESSION['flash_error'] = "Error loading cart.";
            }
        }
    }
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    
    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-8">Shopping Cart</h1>
    
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="bg-green-50 text-green-700 p-4 rounded-xl text-sm mb-6 border border-green-100 flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <?php echo h($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['flash_error']) || $out_of_stock_warnings): ?>
        <div class="bg-red-50 text-red-700 p-4 rounded-xl text-sm mb-6 border border-red-100 flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <?php 
                if (isset($_SESSION['flash_error'])) {
                    echo h($_SESSION['flash_error']); unset($_SESSION['flash_error']); 
                } else {
                    echo "Some items in your cart have stock changes. Please review your cart.";
                }
            ?>
        </div>
    <?php endif; ?>

    <?php if (empty($cart_items)): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center max-w-2xl mx-auto">
            <div class="w-20 h-20 bg-gray-50 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Your Cart is Empty</h2>
            <p class="text-gray-500 mb-8">Looks like you haven't added any phones to your cart yet.</p>
            <a href="<?php echo base_url('phones/'); ?>" class="inline-block bg-primary text-white font-semibold px-8 py-3.5 rounded-xl shadow hover:bg-gray-800 transition">Start Shopping</a>
        </div>
    <?php else: ?>
        
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Cart Items -->
            <div class="lg:w-2/3 flex flex-col gap-4">
                
                <div class="flex justify-end mb-2">
                    <form action="<?php echo base_url('cart/action.php'); ?>" method="POST" id="form-clear-cart" data-custom-submit="true">
                        <input type="hidden" name="action" value="clear">
                        <button type="button" class="text-sm font-medium text-error hover:underline flex items-center gap-1" onclick="openModal('Are you sure you want to remove all items from your cart?', 'Clear Cart', 'Cancel', true, () => document.getElementById('form-clear-cart').submit())">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Clear Cart
                        </button>
                    </form>
                </div>
                
                <?php foreach ($cart_items as $item): ?>
                    <div class="bg-white p-4 sm:p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-col sm:flex-row gap-4 sm:gap-6 <?php echo $item['is_unavailable'] ? 'opacity-60' : ''; ?>">
                        
                        <!-- Product Image -->
                        <div class="w-24 h-24 sm:w-32 sm:h-32 flex-shrink-0 bg-gray-50 rounded-xl flex items-center justify-center p-2 border border-gray-100 self-center sm:self-start">
                            <?php if(!empty($item['phone']['image'])): ?>
                                <img src="<?php echo base_url('assets/images/' . h($item['phone']['image'])); ?>" alt="<?php echo h($item['phone']['model']); ?>" class="max-w-full max-h-full object-contain">
                            <?php else: ?>
                                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Product Details & Controls -->
                        <div class="flex-grow flex flex-col">
                            <div class="flex justify-between items-start mb-1">
                                <div>
                                    <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1"><?php echo h($item['phone']['brand']); ?></div>
                                    <h3 class="text-base sm:text-lg font-bold text-gray-900 leading-tight flex items-center gap-2">
                                        <a href="<?php echo base_url('phones/details.php?id=' . $item['phone']['id']); ?>" class="hover:text-secondary transition-colors"><?php echo h($item['phone']['model']); ?></a>
                                    </h3>
                                    <?php if ($item['phone']['variant_id']): ?>
                                        <div class="mt-1 flex items-center gap-2">
                                            <span class="inline-block bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-md font-medium">
                                                <?php echo h($item['phone']['storage']); ?> 
                                                <?php echo $item['phone']['color'] ? ' - ' . h($item['phone']['color']) : ''; ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-right ml-4">
                                    <span class="block font-bold text-gray-900">₦<?php echo number_format($item['subtotal']); ?></span>
                                    <span class="block text-xs text-gray-500">₦<?php echo number_format($item['price']); ?> each</span>
                                </div>
                            </div>
                            
                            <?php if ($item['is_unavailable']): ?>
                                <div class="mt-2 text-sm font-bold text-error">Currently Unavailable</div>
                                <div class="mt-auto pt-4 flex justify-end">
                                    <form action="<?php echo base_url('cart/action.php'); ?>" method="POST" id="form-remove-<?php echo $item['phone']['id'] . '-' . $item['phone']['variant_id']; ?>" data-custom-submit="true">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="phone_id" value="<?php echo $item['phone']['id']; ?>">
                                        <?php if ($item['phone']['variant_id']): ?>
                                            <input type="hidden" name="variant_id" value="<?php echo $item['phone']['variant_id']; ?>">
                                        <?php endif; ?>
                                        <button type="button" class="text-sm font-medium text-gray-400 hover:text-error transition" onclick="openModal('Remove this phone from your cart?', 'Remove', 'Cancel', true, () => document.getElementById('form-remove-<?php echo $item['phone']['id'] . '-' . $item['phone']['variant_id']; ?>').submit())">Remove</button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="mt-auto pt-4 flex items-center justify-between">
                                    <!-- Quantity Controls -->
                                    <form action="<?php echo base_url('cart/action.php'); ?>" method="POST" class="flex items-center border border-gray-300 rounded-lg overflow-hidden h-10 w-32" id="form-update-<?php echo $item['phone']['id'] . '-' . $item['phone']['variant_id']; ?>">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="phone_id" value="<?php echo $item['phone']['id']; ?>">
                                        <?php if ($item['phone']['variant_id']): ?>
                                            <input type="hidden" name="variant_id" value="<?php echo $item['phone']['variant_id']; ?>">
                                        <?php endif; ?>
                                        <button type="button" class="w-10 h-full bg-gray-50 text-gray-600 hover:bg-gray-100 focus:outline-none transition" onclick="const input = this.nextElementSibling; if(input.value > 1) { input.value--; this.form.submit(); } else { openModal('Remove this phone from your cart?', 'Remove', 'Cancel', true, () => document.getElementById('form-remove-<?php echo $item['phone']['id'] . '-' . $item['phone']['variant_id']; ?>').submit()); }">-</button>
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" class="w-full text-center text-sm font-semibold text-gray-900 border-none p-0 focus:ring-0" readonly onchange="this.form.submit()">
                                        <button type="button" class="w-10 h-full bg-gray-50 text-gray-600 hover:bg-gray-100 focus:outline-none transition" onclick="const input = this.previousElementSibling; if(input.value < <?php echo $item['stock']; ?>) { input.value++; this.form.submit(); } else { alert('Only <?php echo $item['stock']; ?> units available.'); }">+</button>
                                    </form>
                                    
                                    <!-- Remove Button -->
                                    <form action="<?php echo base_url('cart/action.php'); ?>" method="POST" id="form-remove-<?php echo $item['phone']['id'] . '-' . $item['phone']['variant_id']; ?>" data-custom-submit="true">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="phone_id" value="<?php echo $item['phone']['id']; ?>">
                                        <?php if ($item['phone']['variant_id']): ?>
                                            <input type="hidden" name="variant_id" value="<?php echo $item['phone']['variant_id']; ?>">
                                        <?php endif; ?>
                                        <button type="button" class="text-sm font-medium text-error hover:text-red-700 transition flex items-center gap-1" onclick="openModal('Remove this phone from your cart?', 'Remove', 'Cancel', true, () => document.getElementById('form-remove-<?php echo $item['phone']['id'] . '-' . $item['phone']['variant_id']; ?>').submit())">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            <span class="hidden sm:inline">Remove</span>
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                            
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Order Summary -->
            <div class="lg:w-1/3">
                <div class="bg-gray-50 rounded-2xl p-6 border border-gray-200 sticky top-24">
                    <h2 class="text-lg font-bold text-gray-900 mb-6">Order Summary</h2>
                    
                    <div class="space-y-3 text-sm mb-6 border-b border-gray-200 pb-6">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal (<?php echo get_cart_count(); ?> items)</span>
                            <span class="font-medium text-gray-900">₦<?php echo number_format($cart_total); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Shipping</span>
                            <span class="font-medium text-gray-900">Calculated at checkout</span>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-end mb-8">
                        <span class="text-base font-bold text-gray-900">Total</span>
                        <span class="text-2xl font-black text-gray-900">₦<?php echo number_format($cart_total); ?></span>
                    </div>
                    
                    <a href="<?php echo base_url('checkout/'); ?>" class="w-full bg-primary text-white font-bold py-3.5 px-6 rounded-xl shadow hover:bg-gray-800 transition flex items-center justify-center gap-2 <?php echo $out_of_stock_warnings ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''; ?>">
                        Proceed to Checkout
                    </a>
                    
                    <?php if ($out_of_stock_warnings): ?>
                    <p class="text-xs text-error mt-3 text-center">Please remove unavailable items to proceed.</p>
                    <?php endif; ?>
                    
                </div>
            </div>
        </div>
        
    <?php endif; ?>

</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
