<?php
require_once dirname(__DIR__) . '/config/database.php';

// Redirect if already logged in
if (is_logged_in()) {
    header("Location: " . base_url('account/'));
    exit;
}

$error = '';
$full_name = '';
$email = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($full_name)) {
        $error = 'Please enter your full name.';
    } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (empty($phone)) {
        $error = 'Please enter your phone number.';
    } elseif (strlen($password) < 6) {
        $error = 'Please choose a stronger password.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        try {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'An account with this email already exists.';
            } else {
                // Insert user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, phone, role) VALUES (?, ?, ?, ?, 'customer')");
                $stmt->execute([$full_name, $email, $hashed_password, $phone]);
                
                $new_user_id = $pdo->lastInsertId();
                
                // Send welcome notification
                require_once dirname(__DIR__) . '/includes/notifications.php';
                create_notification(
                    $pdo, 
                    $new_user_id, 
                    'Welcome to Phone Marketplace', 
                    "Welcome, " . $full_name . "! Your account has been successfully created. You can now track your orders and save your favorite phones.", 
                    'account'
                );
                
                $_SESSION['flash_success'] = "Account created successfully. You can now log in.";
                header("Location: " . base_url('auth/login.php'));
                exit;
            }
        } catch (PDOException $e) {
            $error = 'We couldn\'t create your account. Please try again.';
        }
    }
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex justify-center">
    <div class="bg-white p-8 rounded-xl shadow-md border border-gray-100 w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Create an Account</h1>
            <p class="text-sm text-gray-500 mt-2">Join PhoneHub to save your favorite phones.</p>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm mb-6 border border-red-100 flex items-start gap-2">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span><?php echo h($error); ?></span>
            </div>
        <?php endif; ?>
        
        <form class="space-y-4" method="POST" action="" data-custom-submit="true">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                <input type="text" name="full_name" value="<?php echo h($full_name); ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                <input type="email" name="email" value="<?php echo h($email); ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                <input type="tel" name="phone" value="<?php echo h($phone); ?>" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                <input type="password" name="password" required minlength="6" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
                <input type="password" name="confirm_password" required minlength="6" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary text-sm">
            </div>
            <button type="submit" class="w-full bg-primary text-white font-semibold py-2.5 rounded-lg shadow hover:bg-gray-800 transition duration-150" onclick="setTimeout(() => { this.disabled = true; this.form.submit(); }, 10);">
                Register
            </button>
        </form>
        
        <div class="mt-6 text-center text-sm text-gray-600">
            Already have an account? <a href="<?php echo base_url('auth/login.php'); ?>" class="font-medium text-secondary hover:text-blue-700">Sign in</a>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
