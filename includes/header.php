<?php
require_once __DIR__ . '/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Phone Marketplace</title>
    <!-- Tailwind CSS (CDN for setup phase) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#111827',
                        secondary: '#2563eb',
                        accent: '#f59e0b',
                        background: '#f8fafc',
                        card: '#ffffff',
                        success: '#10b981',
                        error: '#ef4444'
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo base_url('assets/css/style.css'); ?>">
    <!-- Google Fonts for professional look -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-background text-gray-800 antialiased font-sans flex flex-col min-h-screen pb-16 md:pb-0">
    
    <!-- Navigation header -->
    <header class="bg-primary text-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                
                <!-- Mobile Hamburger & Logo -->
                <div class="flex items-center gap-3">
                    <button class="md:hidden text-gray-300 hover:text-white focus:outline-none" id="mobile-menu-button">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <a href="<?php echo base_url(); ?>" class="text-xl font-bold text-white flex items-center gap-2">
                        <svg class="w-6 h-6 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        <span class="hidden sm:block">PhoneHub</span>
                    </a>
                </div>

                <!-- Search Bar (Desktop/Tablet) -->
                <form action="<?php echo base_url('phones/'); ?>" method="GET" class="hidden md:flex flex-1 max-w-xl mx-8 relative text-gray-900">
                    <input type="text" name="q" value="<?php echo isset($_GET['q']) ? h($_GET['q']) : ''; ?>" placeholder="Search phones, brands, models..." class="w-full bg-white rounded-full py-2 pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-secondary text-sm h-10">
                    <button type="submit" class="absolute right-3 top-2.5 text-gray-500 hover:text-secondary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </form>
                
                <!-- Icons (Notification, Cart, User) -->
                <div class="flex items-center space-x-3 sm:space-x-5">
                    <a href="<?php echo base_url('notifications/'); ?>" class="text-gray-300 hover:text-white transition-colors relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <?php 
                        if (is_logged_in() && isset($pdo)) {
                            $unread_count = get_unread_notification_count($pdo, get_current_user_id());
                            if ($unread_count > 0): 
                        ?>
                        <span class="absolute -top-1 -right-1 bg-error text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center shadow-sm"><?php echo $unread_count > 99 ? '99+' : $unread_count; ?></span>
                        <?php 
                            endif; 
                        }
                        ?>
                    </a>
                    <a href="<?php echo base_url('cart/'); ?>" class="relative text-gray-600 hover:text-secondary transition-colors p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <?php $cart_count = get_cart_count(); if ($cart_count > 0): ?>
                        <span class="absolute top-0 right-0 bg-error text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center transform translate-x-1 -translate-y-1 shadow-sm"><?php echo $cart_count > 99 ? '99+' : $cart_count; ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <?php if (is_logged_in()): ?>
                        <div class="relative group hidden md:block">
                            <a href="<?php echo base_url('account/'); ?>" class="flex items-center gap-2 text-gray-300 hover:text-white transition-colors p-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                <span class="text-sm font-medium"><?php echo h(explode(' ', $_SESSION['user_name'])[0]); ?></span>
                            </a>
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden group-hover:block border border-gray-100">
                                <a href="<?php echo base_url('account/'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My Account</a>
                                <a href="<?php echo base_url('account/favorites.php'); ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Favorites</a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <button type="button" onclick="openModal('Are you sure you want to log out?', 'Log Out', 'Cancel', false, () => window.location.href='<?php echo base_url('auth/logout.php'); ?>')" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">Log Out</button>
                            </div>
                        </div>
                        <a href="<?php echo base_url('account/'); ?>" class="md:hidden relative text-gray-600 hover:text-secondary transition-colors p-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </a>
                    <?php else: ?>
                        <div class="hidden md:flex items-center gap-3">
                            <a href="<?php echo base_url('auth/login.php'); ?>" class="text-sm font-medium text-gray-300 hover:text-white transition">Log In</a>
                            <a href="<?php echo base_url('auth/register.php'); ?>" class="text-sm font-medium bg-secondary text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition shadow-sm">Sign Up</a>
                        </div>
                        <a href="<?php echo base_url('auth/login.php'); ?>" class="md:hidden relative text-gray-600 hover:text-secondary transition-colors p-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Desktop Navigation Bar -->
        <div class="hidden md:block bg-gray-900 border-t border-gray-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <nav class="flex space-x-8 text-sm font-medium">
                    <a href="<?php echo base_url(); ?>" class="text-white hover:text-secondary px-3 py-3 border-b-2 border-secondary">Home</a>
                    <a href="<?php echo base_url('phones/'); ?>" class="text-gray-300 hover:text-white px-3 py-3 border-b-2 border-transparent hover:border-gray-300">Phones</a>
                    <a href="<?php echo base_url('phones/?latest=1'); ?>" class="text-gray-300 hover:text-white px-3 py-3 border-b-2 border-transparent hover:border-gray-300">Latest Phones</a>
                    <a href="<?php echo base_url('phones/?deals=1'); ?>" class="text-accent hover:text-yellow-400 px-3 py-3 border-b-2 border-transparent hover:border-yellow-400">Deals</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Mobile Side Navigation (Hidden by default) -->
    <div id="mobile-sidebar" class="fixed inset-0 z-50 bg-black bg-opacity-50 hidden md:hidden">
        <div class="bg-primary w-64 h-full shadow-xl flex flex-col absolute left-0 top-0 transform transition-transform duration-300 -translate-x-full" id="mobile-sidebar-content">
            <div class="p-4 border-b border-gray-800 flex justify-between items-center text-white">
                <span class="text-lg font-bold flex items-center gap-2">
                    <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    Menu
                </span>
                <button id="mobile-menu-close" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <?php if (is_logged_in()): ?>
                <div class="px-4 py-5 bg-gray-800 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-secondary flex items-center justify-center text-white font-bold text-lg">
                        <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                    </div>
                    <div>
                        <div class="text-white font-medium text-sm"><?php echo h($_SESSION['user_name']); ?></div>
                        <a href="<?php echo base_url('account/'); ?>" class="text-secondary text-xs hover:underline">View Account</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="px-4 py-5 bg-gray-800 flex gap-2">
                    <a href="<?php echo base_url('auth/login.php'); ?>" class="flex-1 bg-white text-primary text-center py-2 rounded font-medium text-sm">Log In</a>
                    <a href="<?php echo base_url('auth/register.php'); ?>" class="flex-1 bg-secondary text-white text-center py-2 rounded font-medium text-sm">Sign Up</a>
                </div>
            <?php endif; ?>
            
            <nav class="flex-grow py-4 px-2 space-y-1 overflow-y-auto">
                <a href="<?php echo base_url(); ?>" class="block px-3 py-2 rounded-md text-base font-medium text-white bg-gray-800">Home</a>
                <a href="<?php echo base_url('phones/'); ?>" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-800">Phones</a>
                <a href="<?php echo base_url('phones/?latest=1'); ?>" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-800">Latest Phones</a>
                <a href="<?php echo base_url('phones/?deals=1'); ?>" class="block px-3 py-2 rounded-md text-base font-medium text-accent hover:text-yellow-400 hover:bg-gray-800">Deals</a>
                <div class="border-t border-gray-800 my-2 pt-2"></div>
                
                <?php if (is_logged_in()): ?>
                    <a href="<?php echo base_url('orders/'); ?>" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-800">My Orders</a>
                    <a href="<?php echo base_url('account/favorites.php'); ?>" class="block px-3 py-2 rounded-md text-base font-medium text-gray-300 hover:text-white hover:bg-gray-800">Favorites</a>
                    <button type="button" onclick="openModal('Are you sure you want to log out?', 'Log Out', 'Cancel', false, () => window.location.href='<?php echo base_url('auth/logout.php'); ?>')" class="w-full text-left block px-3 py-2 rounded-md text-base font-medium text-red-400 hover:text-red-300 hover:bg-gray-800">Log Out</button>
                <?php endif; ?>
            </nav>
        </div>
    </div>
    
    <!-- Main Content Area -->
    <main class="flex-grow w-full max-w-7xl mx-auto md:px-4 sm:px-6 lg:px-8 bg-background">
