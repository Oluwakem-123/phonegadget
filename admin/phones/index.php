<?php
$page_title = 'Phones Management - Admin';
require_once dirname(__DIR__) . '/includes/header.php';

// Pagination and Filters
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$stock = isset($_GET['stock']) ? trim($_GET['stock']) : '';

// Build Query
$where_clauses = ["1=1"];
$params = [];

if ($search !== '') {
    $where_clauses[] = "(brand LIKE ? OR model LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status === 'active' || $status === 'inactive') {
    $where_clauses[] = "status = ?";
    $params[] = $status;
}

if ($stock === 'in') {
    $where_clauses[] = "stock_quantity > 0";
} elseif ($stock === 'out') {
    $where_clauses[] = "stock_quantity <= 0";
}

$where_sql = implode(' AND ', $where_clauses);

// Get Total Count
$count_sql = "SELECT COUNT(*) FROM phones WHERE $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Get Records
$sql = "SELECT * FROM phones WHERE $where_sql ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$phones = $stmt->fetchAll();

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
        <h1 class="text-2xl font-bold text-gray-900">Phones Management</h1>
        <p class="text-sm text-gray-500 mt-1">Manage all devices in the marketplace</p>
    </div>
    <a href="<?php echo base_url('admin/phones/create.php'); ?>" class="bg-secondary text-white font-semibold py-2 px-4 rounded-lg shadow-sm hover:bg-blue-700 transition flex items-center gap-2 text-sm whitespace-nowrap">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Add Phone
    </a>
</div>

<!-- Filters -->
<div class="bg-card rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form action="" method="GET" class="flex flex-col sm:flex-row gap-4">
        <div class="flex-grow">
            <input type="text" name="search" value="<?php echo h($search); ?>" placeholder="Search brand or model..." class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-secondary focus:border-secondary">
        </div>
        <div class="sm:w-48">
            <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-secondary focus:border-secondary">
                <option value="">All Statuses</option>
                <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
        <div class="sm:w-48">
            <select name="stock" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-secondary focus:border-secondary">
                <option value="">All Inventory</option>
                <option value="in" <?php echo $stock === 'in' ? 'selected' : ''; ?>>In Stock</option>
                <option value="out" <?php echo $stock === 'out' ? 'selected' : ''; ?>>Out of Stock</option>
            </select>
        </div>
        <button type="submit" class="bg-gray-800 text-white font-medium py-2 px-6 rounded-lg hover:bg-gray-900 transition text-sm whitespace-nowrap">Filter</button>
        <?php if (!empty($_GET)): ?>
            <a href="?" class="text-gray-500 hover:text-gray-700 font-medium py-2 px-4 text-sm flex items-center justify-center whitespace-nowrap">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Phones Table -->
<div class="bg-card rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Latest</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (count($phones) > 0): ?>
                    <?php foreach ($phones as $p): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded flex items-center justify-center overflow-hidden">
                                    <?php if(!empty($p['image'])): ?>
                                        <img class="h-8 object-contain" src="<?php echo base_url('assets/images/' . h($p['image'])); ?>" alt="">
                                    <?php else: ?>
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    <?php endif; ?>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900"><?php echo h($p['model']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo h($p['brand']); ?> • ID: <?php echo $p['id']; ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 font-medium">₦<?php echo number_format($p['price']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($p['stock_quantity'] > 10): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"><?php echo $p['stock_quantity']; ?></span>
                            <?php elseif ($p['stock_quantity'] > 0): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800"><?php echo $p['stock_quantity']; ?></span>
                            <?php else: ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Out of Stock</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($p['status'] === 'active'): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            <?php else: ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($p['is_latest']): ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Yes</span>
                            <?php else: ?>
                                <span class="text-sm text-gray-500">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-3">
                                <a href="<?php echo base_url('phones/details.php?id=' . $p['id']); ?>" target="_blank" class="text-gray-500 hover:text-secondary" title="View in Store">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <a href="<?php echo base_url('admin/phones/edit.php?id=' . $p['id']); ?>" class="text-secondary hover:text-blue-800" title="Edit Phone">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                <form method="POST" action="<?php echo base_url('admin/phones/action.php'); ?>" class="inline" id="toggle-form-<?php echo $p['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                    <?php if ($p['status'] === 'active'): ?>
                                        <button type="button" onclick="confirmAction('Deactivate this phone? It will no longer be available for purchase.', 'toggle-form-<?php echo $p['id']; ?>')" class="text-error hover:text-red-800" title="Deactivate">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" onclick="confirmAction('Activate this phone? It will become visible in the store.', 'toggle-form-<?php echo $p['id']; ?>')" class="text-success hover:text-green-800" title="Activate">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            No phones found matching your criteria.
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
