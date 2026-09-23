<?php
require_once dirname(__DIR__) . '/config/database.php';

require_login();

$user_id = get_current_user_id();
$order_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$order = null;
$order_items = [];

if ($order_id > 0) {
    try {
        // Verify ownership and get order details
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$order_id, $user_id]);
        $order = $stmt->fetch();
        
        if ($order) {
            // Get order items
            $stmt = $pdo->prepare("
                SELECT oi.*, p.brand, p.model, p.image 
                FROM order_items oi 
                JOIN phones p ON oi.phone_id = p.id 
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$order_id]);
            $order_items = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        $error = "Error loading order details.";
    }
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-16 text-center">
    
    <?php if (!$order): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 max-w-2xl mx-auto">
            <div class="w-20 h-20 bg-red-50 text-error rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Order Not Found</h1>
            <p class="text-gray-500 mb-8">We couldn't find the order you're looking for, or you do not have permission to view it.</p>
            <a href="<?php echo base_url('account/'); ?>" class="inline-block bg-gray-100 text-gray-800 font-semibold px-8 py-3.5 rounded-xl shadow-sm hover:bg-gray-200 transition">Go to Dashboard</a>
        </div>
    <?php else: ?>
        <div class="max-w-3xl mx-auto">
            <div class="w-24 h-24 bg-green-50 text-success rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            
            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">Order Placed Successfully!</h1>
            <p class="text-lg text-gray-600 mb-8">Thank you for your purchase. We are processing your order.</p>
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden text-left mb-8">
                <div class="p-6 md:p-8 border-b border-gray-100 bg-gray-50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <p class="text-sm text-gray-500 font-medium uppercase tracking-wider mb-1">Order Number</p>
                        <p class="text-xl font-bold text-gray-900">#<?php echo str_pad($order['id'], 8, '0', STR_PAD_LEFT); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium uppercase tracking-wider mb-1">Date</p>
                        <p class="text-base font-semibold text-gray-900"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium uppercase tracking-wider mb-1">Total</p>
                        <p class="text-xl font-bold text-gray-900">₦<?php echo number_format($order['total_amount']); ?></p>
                    </div>
                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </div>
                </div>
                
                <div class="p-6 md:p-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b border-gray-100 pb-2">Order Items</h3>
                    <div class="space-y-4 mb-8">
                        <?php foreach ($order_items as $item): ?>
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 bg-gray-50 rounded-lg p-2 flex-shrink-0 border border-gray-100">
                                    <?php if(!empty($item['image'])): ?>
                                        <img src="<?php echo base_url('assets/images/' . h($item['image'])); ?>" class="w-full h-full object-contain">
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow">
                                    <p class="text-xs text-gray-500 uppercase"><?php echo h($item['brand']); ?></p>
                                    <h4 class="text-sm font-bold text-gray-900"><?php echo h($item['model']); ?></h4>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">Qty: <?php echo $item['quantity']; ?></p>
                                    <p class="text-sm font-bold text-gray-900">₦<?php echo number_format($item['subtotal']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b border-gray-100 pb-2">Delivery Information</h3>
                    <div class="bg-gray-50 rounded-xl p-4 text-sm text-gray-700">
                        <p class="font-bold mb-1"><?php echo h($order['customer_name']); ?></p>
                        <p class="mb-1"><?php echo h($order['customer_phone']); ?></p>
                        <p><?php echo nl2br(h($order['delivery_address'])); ?></p>
                        <p><?php echo h($order['delivery_city']) . ', ' . h($order['delivery_state']); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?php echo base_url('orders/'); ?>" class="bg-white border-2 border-gray-200 text-gray-800 font-bold py-3 px-8 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition shadow-sm">View Order History</a>
                <a href="<?php echo base_url('phones/'); ?>" class="bg-primary text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:bg-gray-800 transition">Continue Shopping</a>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
