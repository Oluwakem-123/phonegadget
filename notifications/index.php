<?php
require_once dirname(__DIR__) . '/config/database.php';
require_login();

$user_id = get_current_user_id();
$error = '';
$success = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $action = $_POST['action'] ?? '';
        $notification_id = isset($_POST['notification_id']) ? (int)$_POST['notification_id'] : 0;
        
        switch ($action) {
            case 'mark_read':
                if ($notification_id > 0) {
                    if (mark_notification_as_read($pdo, $user_id, $notification_id)) {
                        // success
                    }
                }
                break;
                
            case 'mark_all_read':
                if (mark_all_notifications_as_read($pdo, $user_id)) {
                    $_SESSION['flash_success'] = 'All notifications marked as read.';
                }
                break;
                
            case 'delete':
                if ($notification_id > 0) {
                    if (delete_notification($pdo, $user_id, $notification_id)) {
                        $_SESSION['flash_success'] = 'Notification deleted.';
                    } else {
                        $error = "We couldn't delete this notification. Please try again.";
                    }
                }
                break;
                
            case 'delete_all':
                if (delete_all_notifications($pdo, $user_id)) {
                    $_SESSION['flash_success'] = 'All notifications have been deleted.';
                } else {
                    $error = "We couldn't delete your notifications. Please try again.";
                }
                break;
        }
        
        // Redirect to avoid form resubmission
        if (empty($error)) {
            header("Location: " . base_url('notifications/'));
            exit;
        }
    }
}

// Pagination setup
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 20;

// Fetch notifications
$result = get_user_notifications($pdo, $user_id, $page, $limit);
$notifications = $result['items'];
$total = $result['total'];
$total_pages = $result['pages'];

$page_title = 'Notifications - PhoneHub';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 gap-4">
        <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
            Notifications
            <?php 
                $unread = get_unread_notification_count($pdo, $user_id);
                if ($unread > 0): 
            ?>
                <span class="bg-secondary text-white text-xs font-bold px-2.5 py-0.5 rounded-full"><?php echo $unread; ?> new</span>
            <?php endif; ?>
        </h1>
        
        <?php if ($total > 0): ?>
        <div class="flex flex-wrap items-center gap-3">
            <?php if ($unread > 0): ?>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="action" value="mark_all_read">
                <button type="submit" class="text-sm font-medium text-secondary hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition">
                    Mark all as read
                </button>
            </form>
            <?php endif; ?>
            
            <form method="POST" action="" onsubmit="return confirm('Delete all notifications? This cannot be undone.');">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <input type="hidden" name="action" value="delete_all">
                <button type="submit" class="text-sm font-medium text-gray-600 hover:text-red-600 hover:bg-red-50 px-3 py-1.5 rounded-lg transition">
                    Delete all
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if ($error): ?>
        <div class="bg-red-50 text-red-600 p-4 rounded-lg text-sm mb-6 border border-red-100">
            <?php echo h($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($total === 0): ?>
        <!-- Empty State -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
            </div>
            <h2 class="text-lg font-bold text-gray-900 mb-2">No notifications yet</h2>
            <p class="text-gray-500 max-w-md mx-auto">You're all caught up. New updates about your orders and account will appear here.</p>
            <a href="<?php echo base_url('phones/'); ?>" class="inline-block mt-6 bg-primary text-white font-medium py-2 px-6 rounded-lg hover:bg-gray-800 transition">
                Start Shopping
            </a>
        </div>
    <?php else: ?>
        <!-- Notification List -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden divide-y divide-gray-100">
            <?php foreach ($notifications as $notification): 
                $is_unread = (int)$notification['is_read'] === 0;
                $icon = '';
                $icon_bg = '';
                
                switch ($notification['type']) {
                    case 'order':
                    case 'order_status':
                        $icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>';
                        $icon_bg = 'bg-blue-100 text-secondary';
                        break;
                    case 'account':
                        $icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>';
                        $icon_bg = 'bg-purple-100 text-purple-600';
                        break;
                    case 'product':
                        $icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>';
                        $icon_bg = 'bg-green-100 text-green-600';
                        break;
                    default:
                        $icon = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>';
                        $icon_bg = 'bg-gray-100 text-gray-600';
                        break;
                }
                
                // Construct linking if available
                $target_url = null;
                if (!empty($notification['order_id'])) {
                    $target_url = base_url('orders/details.php?id=' . (int)$notification['order_id']);
                } elseif (!empty($notification['phone_id'])) {
                    // Check if phone still exists and is active (ideally cached or joined, doing it inline for simplicity)
                    try {
                        $p_stmt = $pdo->prepare("SELECT status FROM phones WHERE id = ?");
                        $p_stmt->execute([(int)$notification['phone_id']]);
                        $p_row = $p_stmt->fetch();
                        if ($p_row && $p_row['status'] === 'active') {
                            $target_url = base_url('phones/details.php?id=' . (int)$notification['phone_id']);
                        }
                    } catch (Exception $e) {}
                }
            ?>
            <div class="p-4 sm:p-6 transition hover:bg-gray-50 <?php echo $is_unread ? 'bg-blue-50/30' : ''; ?>">
                <div class="flex gap-4">
                    <!-- Icon -->
                    <div class="flex-shrink-0 mt-1">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center <?php echo $icon_bg; ?>">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <?php echo $icon; ?>
                            </svg>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-1 sm:gap-4">
                            <h3 class="text-sm font-bold text-gray-900 <?php echo $is_unread ? 'text-black' : ''; ?>">
                                <?php if ($target_url): ?>
                                    <a href="<?php echo $target_url; ?>" class="hover:text-secondary hover:underline focus:outline-none focus:underline"><?php echo h($notification['title']); ?></a>
                                <?php else: ?>
                                    <?php echo h($notification['title']); ?>
                                <?php endif; ?>
                            </h3>
                            <span class="text-xs text-gray-500 whitespace-nowrap">
                                <?php echo format_notification_time($notification['created_at']); ?>
                            </span>
                        </div>
                        
                        <p class="mt-1 text-sm text-gray-600">
                            <?php echo nl2br(h($notification['message'])); ?>
                        </p>
                        
                        <!-- Actions -->
                        <div class="mt-3 flex items-center gap-4">
                            <?php if ($target_url): ?>
                                <a href="<?php echo $target_url; ?>" class="text-xs font-medium text-secondary hover:text-blue-800">
                                    View Details
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($is_unread): ?>
                            <form method="POST" action="" class="inline-block">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="action" value="mark_read">
                                <input type="hidden" name="notification_id" value="<?php echo (int)$notification['id']; ?>">
                                <button type="submit" class="text-xs font-medium text-gray-500 hover:text-gray-900">
                                    Mark as read
                                </button>
                            </form>
                            <?php endif; ?>
                            
                            <form method="POST" action="" class="inline-block ml-auto sm:ml-0" onsubmit="return confirm('Delete this notification?');">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="notification_id" value="<?php echo (int)$notification['id']; ?>">
                                <button type="submit" class="text-xs font-medium text-gray-400 hover:text-red-600 flex items-center gap-1 p-1 rounded hover:bg-red-50" aria-label="Delete notification">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    <span class="sr-only sm:not-sr-only sm:inline">Delete</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="mt-8 flex justify-center">
            <nav class="flex items-center gap-1" aria-label="Pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>" class="px-3 py-2 rounded-md border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition">Previous</a>
                <?php endif; ?>
                
                <?php 
                // Simple pagination display logic
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                if ($start_page > 1) {
                    echo '<a href="?page=1" class="px-3 py-2 rounded-md border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition">1</a>';
                    if ($start_page > 2) echo '<span class="px-2 text-gray-400">...</span>';
                }
                
                for ($i = $start_page; $i <= $end_page; $i++) {
                    $activeClass = $i === $page ? 'bg-secondary text-white border-secondary' : 'border-gray-200 text-gray-600 hover:bg-gray-50';
                    echo '<a href="?page=' . $i . '" class="px-3 py-2 rounded-md border text-sm font-medium transition ' . $activeClass . '">' . $i . '</a>';
                }
                
                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) echo '<span class="px-2 text-gray-400">...</span>';
                    echo '<a href="?page=' . $total_pages . '" class="px-3 py-2 rounded-md border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition">' . $total_pages . '</a>';
                }
                ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="px-3 py-2 rounded-md border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition">Next</a>
                <?php endif; ?>
            </nav>
        </div>
        <?php endif; ?>
        
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
