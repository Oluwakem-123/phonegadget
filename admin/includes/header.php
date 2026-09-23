<?php
require_once dirname(dirname(__DIR__)) . '/config/database.php';
requireAdmin($pdo);

$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
if ($current_dir === 'admin') {
    $active_nav = 'dashboard';
} else {
    $active_nav = $current_dir;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Admin Dashboard - PhoneHub'; ?></title>
    <!-- Use the existing CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#111827',
                        secondary: '#2563EB',
                        accent: '#F59E0B',
                        background: '#F8FAFC',
                        card: '#FFFFFF',
                        success: '#10B981',
                        error: '#EF4444'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-background text-gray-800 font-sans min-h-screen flex flex-col md:flex-row">

    <!-- Mobile Header -->
    <div class="md:hidden bg-primary text-white p-4 flex justify-between items-center z-50 sticky top-0">
        <div class="font-bold text-xl flex items-center gap-2">
            <svg class="w-6 h-6 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            Admin Panel
        </div>
        <button id="admin-mobile-menu-btn" class="text-gray-300 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
    </div>

    <!-- Sidebar Navigation -->
    <aside id="admin-sidebar" class="bg-primary text-white w-64 flex-shrink-0 min-h-screen flex flex-col transform -translate-x-full md:translate-x-0 fixed md:static inset-y-0 left-0 z-40 transition-transform duration-300 ease-in-out">
        <div class="p-6 hidden md:block border-b border-gray-800">
            <div class="font-bold text-xl flex items-center gap-2">
                <svg class="w-6 h-6 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                PhoneHub <span class="text-xs text-gray-400 font-normal uppercase tracking-wider ml-1">Admin</span>
            </div>
        </div>
        
        <div class="flex-grow py-6 px-4 space-y-1 overflow-y-auto">
            <a href="<?php echo base_url('admin/'); ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition <?php echo $active_nav === 'dashboard' ? 'bg-secondary text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                Dashboard
            </a>
            
            <a href="<?php echo base_url('admin/phones/'); ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition <?php echo $active_nav === 'phones' ? 'bg-secondary text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                Phones
            </a>
            
            <a href="<?php echo base_url('admin/orders/'); ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition <?php echo $active_nav === 'orders' ? 'bg-secondary text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                Orders
            </a>
            
            <a href="<?php echo base_url('admin/users/'); ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition <?php echo $active_nav === 'users' ? 'bg-secondary text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Customers
            </a>
            
            <a href="<?php echo base_url('admin/categories/'); ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition <?php echo $active_nav === 'categories' ? 'bg-secondary text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                Categories
            </a>
        </div>
        
        <div class="p-4 border-t border-gray-800">
            <a href="<?php echo base_url(); ?>" class="flex items-center justify-center gap-2 w-full bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Store
            </a>
            <div class="mt-4 text-center text-xs text-gray-500">
                Logged in as <?php echo h($_SESSION['user_name']); ?>
            </div>
        </div>
    </aside>

    <!-- Overlay for mobile sidebar -->
    <div id="admin-sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden md:hidden"></div>

    <!-- Main Content -->
    <main class="flex-grow w-full max-w-full overflow-x-hidden p-4 md:p-8 bg-background flex flex-col min-h-screen">
        
        <!-- Flash Messages -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="bg-green-50 border-l-4 border-success p-4 mb-6 rounded-r-lg shadow-sm" role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-800 font-medium"><?php echo h($_SESSION['flash_success']); ?></p>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="bg-red-50 border-l-4 border-error p-4 mb-6 rounded-r-lg shadow-sm" role="alert">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-800 font-medium"><?php echo h($_SESSION['flash_error']); ?></p>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
