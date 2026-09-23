<?php
$page_title = 'Customers Management - Admin';
require_once dirname(__DIR__) . '/includes/header.php';

// Pagination and Filters
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

// Build Query
// We only want to manage standard customers (or potentially other admins but usually customers in this view)
$where_clauses = ["role = 'customer'"];
$params = [];

if ($search !== '') {
    $where_clauses[] = "(full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($status === 'active' || $status === 'suspended' || $status === 'deactivated') {
    $where_clauses[] = "status = ?";
    $params[] = $status;
} elseif ($status === 'soft_deleted') {
    $where_clauses[] = "deleted_at IS NOT NULL";
} else {
    // If we aren't specifically looking for soft_deleted, we should probably still show them but marked, or hide them.
    // The requirement says "Customer-facing account deletion remains soft deletion... remain inaccessible". 
    // Let's show all by default if no filter, or maybe keep them visible but marked.
}

$where_sql = implode(' AND ', $where_clauses);

// Get Total Count
$count_sql = "SELECT COUNT(*) FROM users WHERE $where_sql";
$stmt = $pdo->prepare($count_sql);
$stmt->execute($params);
$total_records = $stmt->fetchColumn();
$total_pages = ceil($total_records / $limit);

// Get Records
$sql = "SELECT id, full_name, email, phone, role, status, created_at, last_login_at, email_verified_at, deactivated_at, deleted_at 
        FROM users 
        WHERE $where_sql 
        ORDER BY id DESC 
        LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

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
        <h1 class="text-2xl font-bold text-gray-900">Customers Management</h1>
        <p class="text-sm text-gray-500 mt-1">Manage user accounts and lifecycle</p>
    </div>
</div>

<!-- Filters -->
<div class="bg-card rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <form action="" method="GET" class="flex flex-col sm:flex-row gap-4">
        <div class="flex-grow">
            <input type="text" name="search" value="<?php echo h($search); ?>" placeholder="Search name, email, or phone..." class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-secondary focus:border-secondary">
        </div>
        <div class="sm:w-48">
            <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:ring-secondary focus:border-secondary">
                <option value="">All Statuses</option>
                <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="suspended" <?php echo $status === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
                <option value="deactivated" <?php echo $status === 'deactivated' ? 'selected' : ''; ?>>Deactivated</option>
                <option value="soft_deleted" <?php echo $status === 'soft_deleted' ? 'selected' : ''; ?>>Deleted (Soft)</option>
            </select>
        </div>
        <button type="submit" class="bg-gray-800 text-white font-medium py-2 px-6 rounded-lg hover:bg-gray-900 transition text-sm whitespace-nowrap">Filter</button>
        <?php if (!empty($_GET)): ?>
            <a href="?" class="text-gray-500 hover:text-gray-700 font-medium py-2 px-4 text-sm flex items-center justify-center whitespace-nowrap">Clear</a>
        <?php endif; ?>
    </form>
</div>

<!-- Users Table -->
<div class="bg-card rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Login</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $u): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-full flex items-center justify-center">
                                    <span class="text-gray-600 font-medium"><?php echo strtoupper(substr($u['full_name'], 0, 1)); ?></span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo h($u['full_name']); ?>
                                        <?php if ($u['deleted_at']): ?>
                                            <span class="ml-2 px-1.5 py-0.5 text-[10px] bg-red-100 text-red-800 rounded">Soft Deleted</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-xs text-gray-500"><?php echo h($u['email']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php 
                                if ($u['deleted_at']) {
                                    echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Deleted</span>';
                                } else {
                                    $status_classes = [
                                        'active' => 'bg-green-100 text-green-800',
                                        'suspended' => 'bg-yellow-100 text-yellow-800',
                                        'deactivated' => 'bg-gray-100 text-gray-800'
                                    ];
                                    $class = $status_classes[$u['status']] ?? 'bg-gray-100 text-gray-800';
                                    echo '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' . $class . '">' . ucfirst($u['status']) . '</span>';
                                }
                            ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('M d, Y', strtotime($u['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $u['last_login_at'] ? date('M d, Y', strtotime($u['last_login_at'])) : 'Never'; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <form method="POST" action="<?php echo base_url('admin/users/action.php'); ?>" class="inline flex items-center justify-end gap-2" id="action-form-<?php echo $u['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                
                                <?php if ($u['deleted_at']): ?>
                                    <!-- Soft deleted users cannot be managed normally according to rules -->
                                    <span class="text-xs text-gray-400">Locked</span>
                                <?php else: ?>
                                    <?php if ($u['status'] === 'active'): ?>
                                        <button type="submit" name="action" value="suspend" onclick="return confirm('Suspend this customer? They will not be able to log in.');" class="text-yellow-600 hover:text-yellow-900 bg-yellow-50 px-2 py-1 rounded text-xs">Suspend</button>
                                        <button type="submit" name="action" value="deactivate" onclick="return confirm('Deactivate this customer?');" class="text-gray-600 hover:text-gray-900 bg-gray-100 px-2 py-1 rounded text-xs">Deactivate</button>
                                    <?php elseif ($u['status'] === 'suspended'): ?>
                                        <button type="submit" name="action" value="restore" onclick="return confirm('Restore this customer? They will be able to log in again.');" class="text-green-600 hover:text-green-900 bg-green-50 px-2 py-1 rounded text-xs">Restore</button>
                                    <?php elseif ($u['status'] === 'deactivated'): ?>
                                        <button type="submit" name="action" value="restore" onclick="return confirm('Restore this deactivated account?');" class="text-green-600 hover:text-green-900 bg-green-50 px-2 py-1 rounded text-xs">Restore</button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            No customers found matching your criteria.
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
