<?php
require_once dirname(__DIR__) . '/config/database.php';
require_login();

$user_id = get_current_user_id();
$error = '';

try {
    $stmt = $pdo->prepare("SELECT full_name, email, phone FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        session_destroy();
        header("Location: " . base_url('auth/login.php'));
        exit;
    }
} catch (PDOException $e) {
    die("Error loading account data.");
}

$full_name = $user['full_name'];
$phone = $user['phone'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($full_name)) {
        $error = 'Full name is required.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
            $stmt->execute([$full_name, $phone, $user_id]);
            
            // Update session name if it changed
            $_SESSION['user_name'] = $full_name;
            
            $_SESSION['flash_success'] = "Profile updated successfully.";
            header("Location: " . base_url('account/'));
            exit;
        } catch (PDOException $e) {
            $error = 'We couldn\'t update your profile. Please try again.';
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
                    <a href="<?php echo base_url('account/profile.php'); ?>" class="px-6 py-4 flex items-center gap-3 text-secondary font-medium bg-gray-50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        Edit Profile
                    </a>
                    <a href="<?php echo base_url('account/password.php'); ?>" class="px-6 py-4 flex items-center gap-3 text-gray-600 hover:text-secondary hover:bg-gray-50 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        Change Password
                    </a>
                    <a href="<?php echo base_url('account/deactivate.php'); ?>" class="px-6 py-4 flex items-center gap-3 text-red-600 hover:bg-red-50 transition border-t border-gray-100">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6"></path></svg>
                        Deactivate Account
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
            
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Profile</h1>
            
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8">
                
                <?php if ($error): ?>
                    <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm mb-6 border border-red-100 flex items-start gap-2">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span><?php echo h($error); ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" data-custom-submit="true" class="space-y-6 max-w-xl">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" value="<?php echo h($user['email']); ?>" disabled class="w-full border border-gray-200 bg-gray-50 text-gray-500 rounded-lg px-4 py-2 text-sm cursor-not-allowed">
                        <p class="text-xs text-gray-500 mt-1">Email address cannot be changed currently.</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                        <input type="text" name="full_name" value="<?php echo h($full_name); ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input type="tel" name="phone" value="<?php echo h($phone); ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary text-sm">
                    </div>
                    
                    <div class="pt-4 flex items-center justify-end gap-3">
                        <a href="<?php echo base_url('account/'); ?>" class="text-gray-500 font-medium text-sm hover:text-gray-800">Cancel</a>
                        <button type="submit" class="bg-primary text-white font-semibold py-2.5 px-6 rounded-lg shadow hover:bg-gray-800 transition duration-150" onclick="setTimeout(() => { this.disabled = true; this.form.submit(); }, 10);">
                            Save Changes
                        </button>
                    </div>
                    
                </form>
                
            </div>
            
            <div class="mt-8 bg-white rounded-2xl shadow-sm border border-red-100 p-6 md:p-8">
                <h2 class="text-lg font-bold text-gray-900 mb-2">Danger Zone</h2>
                <p class="text-sm text-gray-500 mb-4">Deactivating your account logs you out immediately but can be restored later. Deleting your account is permanent.</p>
                <div class="flex flex-wrap gap-4">
                    <a href="<?php echo base_url('account/deactivate.php'); ?>" class="inline-flex items-center justify-center px-4 py-2 border border-red-200 text-sm font-medium rounded-md text-red-600 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition">
                        Deactivate Account
                    </a>
                    <a href="<?php echo base_url('account/delete.php'); ?>" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition shadow-sm">
                        Delete Account
                    </a>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
