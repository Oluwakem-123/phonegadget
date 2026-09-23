<?php
require_once dirname(__DIR__) . '/config/database.php';
require_login();

$user_id = get_current_user_id();

try {
    $stmt = $pdo->prepare("SELECT full_name, email, phone, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // User not found in DB but logged in (should not happen)
        session_destroy();
        header("Location: " . base_url('auth/login.php'));
        exit;
    }
} catch (PDOException $e) {
    die("Error loading account data.");
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    
    <div class="flex flex-col md:flex-row gap-8">
        
        <!-- Sidebar Navigation -->
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 bg-primary text-white">
                    <h2 class="text-xl font-bold"><?php echo h($user['full_name']); ?></h2>
                    <p class="text-sm text-gray-300 mt-1"><?php echo h($user['email']); ?></p>
                </div>
                <nav class="flex flex-col divide-y divide-gray-100">
                    <a href="<?php echo base_url('account/'); ?>" class="px-6 py-4 flex items-center gap-3 text-secondary font-medium bg-gray-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Dashboard
                    </a>
                    <a href="<?php echo base_url('orders/'); ?>" class="px-6 py-4 flex items-center gap-3 text-gray-600 hover:text-secondary hover:bg-gray-50 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        My Orders
                    </a>
                    <a href="<?php echo base_url('account/profile.php'); ?>" class="px-6 py-4 flex items-center gap-3 text-gray-600 hover:text-secondary hover:bg-gray-50 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        Edit Profile
                    </a>
                    <a href="<?php echo base_url('account/password.php'); ?>" class="px-6 py-4 flex items-center gap-3 text-gray-600 hover:text-secondary hover:bg-gray-50 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        Change Password
                    </a>
                    <a href="<?php echo base_url('account/favorites.php'); ?>" class="px-6 py-4 flex items-center gap-3 text-gray-600 hover:text-secondary hover:bg-gray-50 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        My Favorites
                    </a>
                    <a href="<?php echo base_url('cart/'); ?>" class="px-6 py-4 flex items-center gap-3 text-gray-600 hover:text-secondary hover:bg-gray-50 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Shopping Cart
                    </a>
                </nav>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="w-full md:w-3/4">
            
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Account Overview</h1>
            
            <?php if (isset($_SESSION['flash_success'])): ?>
                <div class="bg-green-50 text-green-700 p-4 rounded-xl text-sm mb-6 border border-green-100 flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <?php echo h($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
                </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 mb-8">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Profile Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Full Name</p>
                        <p class="text-base font-semibold text-gray-900"><?php echo h($user['full_name']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Email Address</p>
                        <p class="text-base font-semibold text-gray-900"><?php echo h($user['email']); ?></p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Phone Number</p>
                        <p class="text-base font-semibold text-gray-900"><?php echo !empty($user['phone']) ? h($user['phone']) : '<span class="text-gray-400 italic">Not provided</span>'; ?></p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Member Since</p>
                        <p class="text-base font-semibold text-gray-900"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></p>
                    </div>
                </div>
                
                <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
                    <a href="<?php echo base_url('account/profile.php'); ?>" class="bg-gray-100 text-gray-800 font-medium px-6 py-2 rounded-lg hover:bg-gray-200 transition">Edit Profile</a>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
