<?php
$page_title = 'Orders Management - Admin';
require_once dirname(__DIR__) . '/includes/header.php';

// Pagination and Filters
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

// Build Query
$where_clauses = ["1=1"];
$params = [];

if ($search !== '') {
    // Search by ID, customer name, or customer phone
    if (is_numeric($search)) {
        $where_clauses[] = "(id = ? OR customer_name LIKE ? OR customer_phone LIKE ?)";
        $params[] = $search;
        $params[] = "%$search%";
        $params[] = "%$search%";
    } else {
        $where_clauses[] = "(customer_name LIKE ? OR customer_phone LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
}

if (in_array($status, ['pending', 'processing', 'completed', 'cancelled'])) {
    $where_clauses[] = "status = ?";
    $params[] = $status;
}

$where_sql = implode(' AND ', $where_clauses);

// Get Total Count
$count_sql = "SELECT COUNT(*) FROM orders WHERE $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Get Records
$sql = "SELECT * FROM orders WHERE $where_sql ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Helper to build query string for pagination links
function build_admin_query_string($params_to_update = []) {
    $params = $_GET;
    foreach ($params_to_update as $key => $value) {
        $params[$key] = $value;
    }
    return http_build_query($params);
}
?>

<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Orders Management</h1>
        <p class="text-sm text-gray-500 mt-1">Track and manage customer orders</p>
    </div>
</div>

<!-- Filters -->
<div class="bg-card rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form action="" method="GET" class="flex flex-col sm:flex-row gap-4">
        <div class="flex-grow">
            <input type="text" name="search" value="<?php echo h($search); ?>" placeholder="Search by Order ID, Name, or Phone..." class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-secondary focus:border-secondary">
        </div>
        <div class="sm:w-48">
            <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-secondary focus:border-secondary">
                <option value="">All Statuses</option>
                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>
        <button type="submit" class="bg-gray-800 text-white font-medium py-2 px-6 rounded-lg hover:bg-gray-900 transition text-sm whitespace-nowrap">Filter</button>
        <?php if (!empty($_GET)): ?>
            <a href="?" class="text-gray-500 hover:text-gray-700 font-medium py-2 px-4 text-sm flex items-center justify-center whitespace-nowrap">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Orders Table -->
<div class="bg-card rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $o): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">#<?php echo $o['id']; ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 font-medium"><?php echo h($o['customer_name']); ?></div>
                            <div class="text-xs text-gray-500"><?php echo h($o['customer_phone']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('M d, Y', strtotime($o['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900">₦<?php echo number_format($o['total_amount']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php 
                                $status_classes = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'processing' => 'bg-blue-100 text-blue-800',
                                    'completed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800'
                                ];
                                $class = $status_classes[$o['status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $class; ?>">
                                <?php echo ucfirst($o['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="<?php echo base_url('admin/orders/details.php?id=' . $o['id']); ?>" class="text-secondary hover:text-blue-800 flex items-center justify-end gap-1">
                                View Details
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            No orders found matching your criteria.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
        <div class="text-sm text-gray-500">
            Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $total_records); ?> of <?php echo $total_records; ?> entries
        </div>
        <div class="flex gap-2">
            <?php if ($page > 1): ?>
                <a href="?<?php echo build_admin_query_string(['page' => $page - 1]); ?>" class="px-3 py-1 border border-gray-300 rounded text-sm font-medium text-gray-700 hover:bg-gray-50">Prev</a>
            <?php endif; ?>
            <?php if ($page < $total_pages): ?>
                <a href="?<?php echo build_admin_query_string(['page' => $page + 1]); ?>" class="px-3 py-1 border border-gray-300 rounded text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
