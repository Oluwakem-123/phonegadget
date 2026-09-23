<?php
require_once dirname(__DIR__) . '/config/database.php';
require_login();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $password = $_POST['password'] ?? '';
        
        if (empty($password)) {
            $error = 'Please enter your password to confirm.';
        } else {
            // Verify password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1");
            $stmt->execute([get_current_user_id()]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Deactivate the account
                try {
                    $pdo->beginTransaction();
                    
                    $update_stmt = $pdo->prepare("UPDATE users SET status = 'deactivated', deactivated_at = CURRENT_TIMESTAMP WHERE id = ? AND deleted_at IS NULL");
                    $update_stmt->execute([get_current_user_id()]);
                    
                    // Send notification
                    require_once dirname(__DIR__) . '/includes/notifications.php';
                    create_notification(
                        $pdo, 
                        get_current_user_id(), 
                        'Account Deactivated', 
                        "Your account has been deactivated. You can request account restoration by contacting support.", 
                        'account'
                    );
                    
                    $pdo->commit();
                    
                    // Log the user out completely
                    $_SESSION = [];
                    session_destroy();
                    session_start();
                    $_SESSION['flash_success'] = 'Your account has been successfully deactivated.';
                    
                    header("Location: " . base_url('auth/login.php'));
                    exit;
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    $error = 'Failed to deactivate account. Please try again later.';
                }
            } else {
                $error = 'Incorrect password.';
            }
        }
    }
}

$page_title = 'Deactivate Account - PhoneHub';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col md:flex-row gap-8">
        
        <!-- Sidebar Navigation -->
        <div class="w-full md:w-64 flex-shrink-0">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="font-bold text-gray-900">My Account</h2>
                    <p class="text-sm text-gray-500 mt-1"><?php echo h($_SESSION['user_name']); ?></p>
                </div>
                <nav class="flex flex-col">
                    <a href="<?php echo base_url('account/'); ?>" class="px-6 py-4 flex items-center gap-3 text-gray-600 hover:text-secondary hover:bg-gray-50 transition">
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
                        Favorites
                    </a>
                    <a href="<?php echo base_url('auth/logout.php'); ?>" class="px-6 py-4 flex items-center gap-3 text-red-600 hover:bg-red-50 transition border-t border-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        Sign Out
                    </a>
                </nav>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1">
            <div class="bg-white rounded-xl shadow-sm border border-red-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
                    <h1 class="text-xl font-bold text-gray-900">Deactivate Account</h1>
                </div>
                
                <div class="p-6">
                    <?php if ($error): ?>
                        <div class="bg-red-50 text-red-600 p-4 rounded-lg text-sm mb-6 border border-red-100 flex items-start gap-3">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <div>
                                <h3 class="font-medium">Action Failed</h3>
                                <p class="mt-1 text-red-500"><?php echo h($error); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Warning</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>Deactivating your account will immediately log you out. You will not be able to log back in until you request an account restoration.</p>
                                    <p class="mt-2">Your historical orders will remain intact, but you will not be able to access your customer dashboard.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="" class="max-w-md">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Enter your password to confirm</label>
                            <input type="password" name="password" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-red-500 focus:ring-1 focus:ring-red-500 text-sm">
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-red-700 transition" onclick="return confirm('Are you absolutely sure you want to deactivate your account?');">
                                Deactivate Account
                            </button>
                            <a href="<?php echo base_url('account/profile.php'); ?>" class="text-gray-500 hover:text-gray-700 text-sm font-medium">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
