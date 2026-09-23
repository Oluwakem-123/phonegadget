<?php
$page_title = 'Edit Category - Admin';
require_once dirname(__DIR__) . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    $_SESSION['flash_error'] = "Category not found.";
    header("Location: " . base_url('admin/categories/'));
    exit;
}

$errors = [];
$form_data = [
    'name' => $category['name'],
    'description' => $category['description']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid security token.";
    } else {
        $form_data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? '')
        ];

        // Validation
        if (empty($form_data['name'])) {
            $errors[] = "Category name is required.";
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
                $stmt->execute([$form_data['name'], $form_data['description'], $id]);
                
                $_SESSION['flash_success'] = "Category updated successfully.";
                header("Location: " . base_url('admin/categories/'));
                exit;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $errors[] = "A category with that name already exists.";
                } else {
                    $errors[] = "Something went wrong. Please try again.";
                }
            }
        }
    }
}
?>

<div class="mb-6 flex items-center gap-4">
    <a href="<?php echo base_url('admin/categories/'); ?>" class="text-gray-500 hover:text-gray-900 bg-white p-2 rounded-lg border border-gray-200 shadow-sm transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Category</h1>
    </div>
</div>

<?php if (!empty($errors)): ?>
<div class="bg-red-50 border-l-4 border-error p-4 mb-6 rounded-r-lg max-w-2xl">
    <div class="flex">
        <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">There were errors with your submission:</h3>
            <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo h($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="bg-card rounded-xl shadow-sm border border-gray-100 overflow-hidden max-w-2xl">
    <form action="" method="POST" class="p-6 sm:p-8">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        
        <div class="mb-6">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Category Name <span class="text-error">*</span></label>
            <input type="text" id="name" name="name" required value="<?php echo h($form_data['name']); ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-secondary focus:border-secondary">
        </div>
        
        <div class="mb-6">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea id="description" name="description" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-secondary focus:border-secondary"><?php echo h($form_data['description']); ?></textarea>
        </div>
        
        <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
            <a href="<?php echo base_url('admin/categories/'); ?>" class="bg-white border border-gray-300 text-gray-700 font-medium py-2 px-6 rounded-lg hover:bg-gray-50 transition">Cancel</a>
            <button type="submit" class="bg-secondary text-white font-medium py-2 px-6 rounded-lg hover:bg-blue-700 transition">Save Changes</button>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
