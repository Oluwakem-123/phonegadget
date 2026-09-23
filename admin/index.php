<?php
$page_title = 'Dashboard - Admin';
require_once __DIR__ . '/includes/header.php';

// Fetch Statistics

// 1. Phones Stats
$phones_stats = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN stock_quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock
    FROM phones
")->fetch();

// 2. Customers Stats
$customers_stats = $pdo->query("
    SELECT COUNT(*) as total 
    FROM users 
    WHERE role = 'customer'
")->fetch();

// 3. Orders Stats
$orders_stats = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
        SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as total_sales
    FROM orders
")->fetch();

$total_sales = $orders_stats['total_sales'] ?: 0;
?>

<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
    <p class="text-sm text-gray-500 mt-1">Overview of your marketplace</p>
</div>

<!-- Key Metrics -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Sales -->
    <div class="bg-card rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-gray-500 text-sm font-medium">Total Sales</h3>
            <div class="w-10 h-10 rounded-full bg-green-50 text-success flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
        </div>
        <div class="text-2xl font-bold text-gray-900">₦<?php echo number_format($total_sales); ?></div>
        <div class="text-xs text-gray-400 mt-1">From completed orders</div>
    </div>
    
    <!-- Total Orders -->
    <div class="bg-card rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-gray-500 text-sm font-medium">Total Orders</h3>
            <div class="w-10 h-10 rounded-full bg-blue-50 text-secondary flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
            </div>
        </div>
        <div class="text-2xl font-bold text-gray-900"><?php echo (int)$orders_stats['total']; ?></div>
        <div class="text-xs text-gray-400 mt-1"><?php echo (int)$orders_stats['pending']; ?> pending, <?php echo (int)$orders_stats['processing']; ?> processing</div>
    </div>
    
    <!-- Total Customers -->
    <div class="bg-card rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-gray-500 text-sm font-medium">Customers</h3>
            <div class="w-10 h-10 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
        </div>
        <div class="text-2xl font-bold text-gray-900"><?php echo (int)$customers_stats['total']; ?></div>
        <div class="text-xs text-gray-400 mt-1">Registered users</div>
    </div>
    
    <!-- Total Phones -->
    <div class="bg-card rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-gray-500 text-sm font-medium">Phones</h3>
            <div class="w-10 h-10 rounded-full bg-yellow-50 text-accent flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            </div>
        </div>
        <div class="text-2xl font-bold text-gray-900"><?php echo (int)$phones_stats['total']; ?></div>
        <div class="text-xs text-gray-400 mt-1 flex gap-2">
            <span class="text-success"><?php echo (int)$phones_stats['active']; ?> active</span>
            <span class="<?php echo $phones_stats['out_of_stock'] > 0 ? 'text-error' : 'text-gray-400'; ?>"><?php echo (int)$phones_stats['out_of_stock']; ?> out of stock</span>
        </div>
    </div>
</div>

<!-- Order Status Breakdown -->
<div class="bg-card rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="font-bold text-gray-900">Order Status</h2>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gray-50 rounded-lg p-4 text-center border border-gray-100">
                <div class="text-gray-500 text-xs font-semibold uppercase tracking-wider mb-1">Pending</div>
                <div class="text-xl font-bold text-yellow-600"><?php echo (int)$orders_stats['pending']; ?></div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center border border-gray-100">
                <div class="text-gray-500 text-xs font-semibold uppercase tracking-wider mb-1">Processing</div>
                <div class="text-xl font-bold text-blue-600"><?php echo (int)$orders_stats['processing']; ?></div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center border border-gray-100">
                <div class="text-gray-500 text-xs font-semibold uppercase tracking-wider mb-1">Completed</div>
                <div class="text-xl font-bold text-green-600"><?php echo (int)$orders_stats['completed']; ?></div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4 text-center border border-gray-100">
                <div class="text-gray-500 text-xs font-semibold uppercase tracking-wider mb-1">Cancelled</div>
                <div class="text-xl font-bold text-red-600"><?php echo (int)$orders_stats['cancelled']; ?></div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
