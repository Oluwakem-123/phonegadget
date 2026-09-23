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
            // Get order items using historical prices
            $stmt = $pdo->prepare("
                SELECT oi.*, p.brand, p.model, p.image 
                FROM order_items oi 
                LEFT JOIN phones p ON oi.phone_id = p.id 
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

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    
    <div class="mb-8 flex items-center justify-between">
        <div>
            <a href="<?php echo base_url('orders/'); ?>" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-secondary mb-2 transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Orders
            </a>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Order Details</h1>
        </div>
    </div>
    
    <?php if (!$order): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center max-w-2xl mx-auto">
            <div class="w-20 h-20 bg-red-50 text-error rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Order Not Found</h2>
            <p class="text-gray-500 mb-8">We couldn't find the order you're looking for, or you do not have permission to view it.</p>
        </div>
    <?php else: ?>
        
        <div class="flex flex-col lg:flex-row gap-8">
            
            <div class="w-full lg:w-2/3 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 md:p-8 border-b border-gray-100 bg-gray-50 flex flex-wrap gap-6 justify-between items-center">
                        <div>
                            <p class="text-sm text-gray-500 font-medium uppercase tracking-wider mb-1">Order Number</p>
                            <p class="text-xl font-bold text-gray-900">#<?php echo str_pad($order['id'], 8, '0', STR_PAD_LEFT); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium uppercase tracking-wider mb-1">Date Placed</p>
                            <p class="text-base font-semibold text-gray-900"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 font-medium uppercase tracking-wider mb-1">Status</p>
                            <?php 
                                $status_classes = [
                                    'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                    'processing' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'shipped' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                    'delivered' => 'bg-green-100 text-green-800 border-green-200',
                                    'cancelled' => 'bg-red-100 text-red-800 border-red-200'
                                ];
                                $sc = $status_classes[$order['status']] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                            ?>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold border <?php echo $sc; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="p-6 md:p-8">
                        <h3 class="text-lg font-bold text-gray-900 mb-6">Items Ordered</h3>
                        <div class="space-y-6">
                            <?php foreach ($order_items as $item): ?>
                                <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center p-4 border border-gray-100 rounded-xl hover:bg-gray-50 transition">
                                    <div class="w-20 h-20 bg-white rounded-lg p-2 flex-shrink-0 border border-gray-100 self-center">
                                        <?php if(!empty($item['image'])): ?>
                                            <img src="<?php echo base_url('assets/images/' . h($item['image'])); ?>" class="w-full h-full object-contain">
                                        <?php else: ?>
                                            <svg class="w-10 h-10 text-gray-300 mx-auto mt-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-grow">
                                        <?php if (!empty($item['brand'])): ?>
                                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1"><?php echo h($item['brand']); ?></p>
                                            <h4 class="text-base font-bold text-gray-900"><a href="<?php echo base_url('phones/details.php?id=' . $item['phone_id']); ?>" class="hover:text-secondary transition"><?php echo h($item['model']); ?></a></h4>
                                        <?php else: ?>
                                            <h4 class="text-base font-bold text-gray-500 italic">Product no longer available</h4>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex sm:flex-col justify-between sm:justify-center w-full sm:w-auto items-center sm:items-end gap-2 sm:gap-1 mt-2 sm:mt-0 pt-4 sm:pt-0 border-t border-gray-100 sm:border-t-0">
                                        <p class="text-sm text-gray-500">Qty: <span class="font-bold text-gray-900"><?php echo $item['quantity']; ?></span></p>
                                        <p class="text-sm text-gray-500">@ ₦<?php echo number_format($item['unit_price']); ?></p>
                                        <p class="text-lg font-bold text-gray-900 sm:mt-2">₦<?php echo number_format($item['subtotal']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="w-full lg:w-1/3 space-y-6">
                
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4">Order Summary</h3>
                    <div class="space-y-3 mb-6 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span>₦<?php echo number_format($order['total_amount']); ?></span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Delivery</span>
                            <span>₦0</span>
                        </div>
                    </div>
                    <div class="border-t border-gray-100 pt-4 flex justify-between items-center">
                        <span class="text-base font-bold text-gray-900">Total</span>
                        <span class="text-2xl font-extrabold text-gray-900">₦<?php echo number_format($order['total_amount']); ?></span>
                    </div>
                </div>
                
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-6 border-b border-gray-100 pb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Delivery Details
                    </h3>
                    <div class="text-sm text-gray-700 space-y-1">
                        <p class="font-bold text-gray-900 mb-2"><?php echo h($order['customer_name']); ?></p>
                        <p class="flex items-center gap-2 text-gray-600 mb-3"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg> <?php echo h($order['customer_phone']); ?></p>
                        <p class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <?php echo nl2br(h($order['delivery_address'])); ?><br>
                            <?php echo h($order['delivery_city']) . ', ' . h($order['delivery_state']); ?>
                        </p>
                    </div>
                </div>
                
            </div>
            
        </div>
        
    <?php endif; ?>

</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
