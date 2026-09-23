<?php
require_once dirname(__DIR__) . '/config/database.php';

require_login();

$user_id = get_current_user_id();
$orders = [];

try {
    $stmt = $pdo->prepare("
        SELECT id, total_amount, status, created_at 
        FROM orders 
        WHERE user_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll();
    
    // Attach item counts
    if (!empty($orders)) {
        $stmt = $pdo->prepare("SELECT order_id, SUM(quantity) as items_count FROM order_items WHERE order_id IN (" . implode(',', array_column($orders, 'id')) . ") GROUP BY order_id");
        $stmt->execute();
        $counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        foreach ($orders as &$order) {
            $order['items_count'] = $counts[$order['id']] ?? 0;
        }
    }
} catch (PDOException $e) {
    $error = "Error loading order history.";
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">My Orders</h1>
        <a href="<?php echo base_url('account/'); ?>" class="text-secondary font-medium hover:underline text-sm">Back to Account</a>
    </div>

    <?php if (empty($orders)): ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center max-w-2xl mx-auto">
            <div class="w-20 h-20 bg-gray-50 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-4">You haven't placed any orders yet</h2>
            <p class="text-gray-500 mb-8">When you purchase a phone, your orders will appear here.</p>
            <a href="<?php echo base_url('phones/'); ?>" class="inline-block bg-primary text-white font-semibold px-8 py-3.5 rounded-xl shadow hover:bg-gray-800 transition">Start Shopping</a>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left whitespace-nowrap">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Order ID</th>
                            <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Items</th>
                            <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                            <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($orders as $order): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="font-bold text-gray-900">#<?php echo str_pad($order['id'], 8, '0', STR_PAD_LEFT); ?></span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php echo $order['items_count']; ?> item(s)
                                </td>
                                <td class="px-6 py-4 font-bold text-gray-900">
                                    ₦<?php echo number_format($order['total_amount']); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php 
                                        $status_classes = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'processing' => 'bg-blue-100 text-blue-800',
                                            'shipped' => 'bg-indigo-100 text-indigo-800',
                                            'delivered' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800'
                                        ];
                                        $sc = $status_classes[$order['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $sc; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="<?php echo base_url('orders/details.php?id=' . $order['id']); ?>" class="text-secondary hover:text-blue-700 font-medium text-sm flex items-center justify-end gap-1">
                                        View Details
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
