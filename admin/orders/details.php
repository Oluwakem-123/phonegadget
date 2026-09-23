<?php
$page_title = 'Order Details - Admin';
require_once dirname(__DIR__) . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch the order
$stmt = $pdo->prepare("
    SELECT o.*, u.email as user_email
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id = ?
");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['flash_error'] = "Order not found.";
    header("Location: " . base_url('admin/orders/'));
    exit;
}

// Fetch order items (using historical unit_price from order_items)
$stmt = $pdo->prepare("
    SELECT oi.*, p.brand, p.model, p.image
    FROM order_items oi
    JOIN phones p ON oi.phone_id = p.id
    WHERE oi.order_id = ?
");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

// Status classes for display
$status_classes = [
    'pending' => 'bg-yellow-100 text-yellow-800',
    'processing' => 'bg-blue-100 text-blue-800',
    'completed' => 'bg-green-100 text-green-800',
    'cancelled' => 'bg-red-100 text-red-800'
];
$status_class = $status_classes[$order['status']] ?? 'bg-gray-100 text-gray-800';
?>

<div class="mb-6 flex items-center justify-between gap-4 flex-wrap">
    <div class="flex items-center gap-4">
        <a href="<?php echo base_url('admin/orders/'); ?>" class="text-gray-500 hover:text-gray-900 bg-white p-2 rounded-lg border border-gray-200 shadow-sm transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900">Order #<?php echo $order['id']; ?></h1>
                <span class="px-3 py-1 inline-flex text-sm leading-5 font-bold rounded-full <?php echo $status_class; ?>">
                    <?php echo ucfirst($order['status']); ?>
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-1">Placed on <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
        </div>
    </div>
    
    <!-- Action Form -->
    <div class="bg-white p-2 rounded-lg shadow-sm border border-gray-100 flex items-center gap-2">
        <form method="POST" action="<?php echo base_url('admin/orders/action.php'); ?>" class="flex items-center gap-2 m-0" id="status-form">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
            
            <label for="status" class="text-sm font-medium text-gray-700 whitespace-nowrap hidden sm:block">Update Status:</label>
            <select name="status" id="status" class="border border-gray-300 rounded px-3 py-1.5 text-sm focus:ring-secondary focus:border-secondary">
                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
            <button type="button" onclick="confirmAction('Change order status? This will notify the customer.', 'status-form')" class="bg-gray-800 text-white font-medium py-1.5 px-4 rounded hover:bg-gray-900 transition text-sm">Save</button>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Left Column: Items -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-card rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-bold text-gray-900">Order Items</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($items as $item): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded flex items-center justify-center overflow-hidden">
                                        <?php if(!empty($item['image'])): ?>
                                            <img class="h-8 object-contain" src="<?php echo base_url('assets/images/' . h($item['image'])); ?>" alt="">
                                        <?php else: ?>
                                            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo h($item['model']); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo h($item['brand']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                ₦<?php echo number_format($item['unit_price']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-900">
                                x<?php echo $item['quantity']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                                ₦<?php echo number_format($item['subtotal']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-500 uppercase">
                                Order Total:
                            </td>
                            <td class="px-6 py-4 text-right text-lg font-bold text-gray-900 whitespace-nowrap">
                                ₦<?php echo number_format($order['total_amount']); ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Right Column: Info -->
    <div class="space-y-6">
        <!-- Customer Details -->
        <div class="bg-card rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-bold text-gray-900">Customer Details</h2>
                <a href="<?php echo base_url('admin/users/?search=' . urlencode($order['user_email'])); ?>" class="text-secondary text-sm hover:underline">View Account</a>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wider mb-1">Name</div>
                    <div class="font-medium text-gray-900"><?php echo h($order['customer_name']); ?></div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wider mb-1">Email Address</div>
                    <div class="text-gray-900"><?php echo h($order['user_email']); ?></div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wider mb-1">Phone Number</div>
                    <div class="text-gray-900"><?php echo h($order['customer_phone']); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Delivery Information -->
        <div class="bg-card rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-bold text-gray-900">Delivery Information</h2>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wider mb-1">City / State</div>
                    <div class="font-medium text-gray-900"><?php echo h($order['delivery_city']); ?>, <?php echo h($order['delivery_state']); ?></div>
                </div>
                <div>
                    <div class="text-xs text-gray-500 uppercase tracking-wider mb-1">Delivery Address</div>
                    <div class="text-gray-900 whitespace-pre-line"><?php echo h($order['delivery_address']); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
