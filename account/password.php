<?php
require_once dirname(__DIR__) . '/config/database.php';
require_login();

$user_id = get_current_user_id();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($current_password, $user['password'])) {
                $error = 'Your current password is incorrect.';
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $pdo->prepare("UPDATE users SET password = ?, password_changed_at = CURRENT_TIMESTAMP WHERE id = ?");
                $update_stmt->execute([$hashed_password, $user_id]);
                
                // Send notification
                require_once dirname(__DIR__) . '/includes/notifications.php';
                create_notification(
                    $pdo, 
                    $user_id, 
                    'Password Changed', 
                    "Your account password was successfully changed. If you did not perform this action, please contact support immediately.", 
                    'account'
                );
                
                $_SESSION['flash_success'] = "Password updated successfully.";
                header("Location: " . base_url('account/'));
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Something went wrong. Please try again.';
        }
    }
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    
    <div class="flex flex-col md:flex-row gap-8">
        
        <!-- Sidebar Navigation -->
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hidden md:block">
                <nav class="flex flex-col divide-y divide-gray-100">
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
                    <a href="<?php echo base_url('account/password.php'); ?>" class="px-6 py-4 flex items-center gap-3 text-secondary font-medium bg-gray-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        Change Password
                    </a>
                </nav>
            </div>
            
            <a href="<?php echo base_url('account/'); ?>" class="md:hidden inline-flex items-center text-sm font-medium text-gray-600 hover:text-secondary mb-4">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Dashboard
            </a>
        </div>
        
        <!-- Main Content -->
        <div class="w-full md:w-3/4">
            
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Change Password</h1>
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                
                <?php if ($error): ?>
                    <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm mb-6 border border-red-100 flex items-start gap-2">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span><?php echo h($error); ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" data-custom-submit="true" class="space-y-6 max-w-xl">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Password *</label>
                        <input type="password" name="current_password" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Password *</label>
                        <input type="password" name="new_password" required minlength="6" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password *</label>
                        <input type="password" name="confirm_password" required minlength="6" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary text-sm">
                    </div>
                    
                    <div class="pt-4 flex items-center justify-end gap-3">
                        <a href="<?php echo base_url('account/'); ?>" class="text-gray-500 font-medium text-sm hover:text-gray-800">Cancel</a>
                        <button type="submit" class="bg-primary text-white font-semibold py-2.5 px-6 rounded-lg shadow hover:bg-gray-800 transition duration-150" onclick="setTimeout(() => { this.disabled = true; this.form.submit(); }, 10);">
                            Update Password
                        </button>
                    </div>
                    
                </form>
                
            </div>
            
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
