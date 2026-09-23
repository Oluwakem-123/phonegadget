<?php
require_once dirname(__DIR__) . '/config/database.php';

// Redirect if already logged in
if (is_logged_in()) {
    header("Location: " . base_url('account/'));
    exit;
}

$error = '';
$flash_error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, full_name, password, role, status, deleted_at FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'active') {
                    $error = 'Your account has been deactivated or suspended.';
                } else {
                    // Login success
                    session_regenerate_id(true); // Prevent session fixation
                    
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['full_name'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    // Update last login
                    $update_stmt = $pdo->prepare("UPDATE users SET last_login_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $update_stmt->execute([$user['id']]);
                    
                    // Merge guest cart to authenticated cart
                    merge_guest_cart($pdo, $user['id']);
                    
                    $_SESSION['flash_success'] = "Login successful.";
                    
                    // Redirect to intended page or account dashboard
                    $redirect = $_SESSION['redirect_url'] ?? base_url('account/');
                    unset($_SESSION['redirect_url']);
                    
                    header("Location: " . $redirect);
                    exit;
                }
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $error = 'Something went wrong. Please try again.';
        }
    }
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex justify-center">
    <div class="bg-white p-8 rounded-xl shadow-md border border-gray-100 w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Sign In</h1>
            <p class="text-sm text-gray-500 mt-2">Welcome back to PhoneHub</p>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm mb-6 border border-red-100">
                <?php echo h($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($flash_error): ?>
            <div class="bg-yellow-50 text-yellow-800 p-3 rounded-lg text-sm mb-6 border border-yellow-100">
                <?php echo h($flash_error); ?>
            </div>
        <?php endif; ?>
        
        <form class="space-y-4" method="POST" action="">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input type="email" name="email" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary text-sm" placeholder="you@example.com">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary text-sm" placeholder="••••••••">
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input type="checkbox" id="remember" class="h-4 w-4 text-secondary focus:ring-secondary border-gray-300 rounded">
                    <label for="remember" class="ml-2 block text-sm text-gray-700">Remember me</label>
                </div>
                <div class="text-sm">
                    <a href="#" class="font-medium text-secondary hover:text-blue-700">Forgot password?</a>
                </div>
            </div>
            <button type="submit" class="w-full bg-primary text-white font-semibold py-2.5 rounded-lg shadow hover:bg-gray-800 transition duration-150">
                Sign In
            </button>
        </form>
        
        <div class="mt-6 text-center text-sm text-gray-600">
            Don't have an account? <a href="<?php echo base_url('auth/register.php'); ?>" class="font-medium text-secondary hover:text-blue-700">Sign up</a>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
