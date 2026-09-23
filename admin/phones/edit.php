<?php
$page_title = 'Edit Phone - Admin';
require_once dirname(__DIR__) . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch the phone
$stmt = $pdo->prepare("SELECT * FROM phones WHERE id = ?");
$stmt->execute([$id]);
$phone = $stmt->fetch();

if (!$phone) {
    $_SESSION['flash_error'] = "Sorry, this phone could not be found.";
    header("Location: " . base_url('admin/phones/'));
    exit;
}

// Fetch categories for the dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();

// Fetch variants
$v_stmt = $pdo->prepare("SELECT * FROM phone_variants WHERE phone_id = ? ORDER BY price ASC");
$v_stmt->execute([$id]);
$existing_variants = $v_stmt->fetchAll();

$errors = [];
$form_data = [
    'brand' => $phone['brand'],
    'model' => $phone['model'],
    'description' => $phone['description'],
    'price' => $phone['price'],
    'stock_quantity' => $phone['stock_quantity'],
    'category_id' => $phone['category_id'],
    'release_date' => $phone['release_date'],
    'is_latest' => $phone['is_latest'],
    'status' => $phone['status']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid security token.";
    } else {
        $form_data = [
            'brand' => trim($_POST['brand'] ?? ''),
            'model' => trim($_POST['model'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price' => $_POST['price'] ?? '',
            'stock_quantity' => $_POST['stock_quantity'] ?? '0',
            'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
            'release_date' => !empty($_POST['release_date']) ? $_POST['release_date'] : null,
            'is_latest' => isset($_POST['is_latest']) ? 1 : 0,
            'status' => in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active'
        ];

        // Validation
        if (empty($form_data['brand'])) $errors[] = "Brand is required.";
        if (empty($form_data['model'])) $errors[] = "Model is required.";
        
        if (!is_numeric($form_data['price']) || $form_data['price'] < 0) {
            $errors[] = "Price must be a positive number.";
        }
        
        if (!is_numeric($form_data['stock_quantity']) || $form_data['stock_quantity'] < 0 || strpos($form_data['stock_quantity'], '.') !== false) {
            $errors[] = "Stock must be a positive integer.";
        }
        
        if (!empty($form_data['release_date']) && !strtotime($form_data['release_date'])) {
            $errors[] = "Invalid release date.";
        }
        
        // Image handling
        $image_name = $phone['image']; // keep existing by default
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            if (in_array($_FILES['image']['type'], $allowed_types)) {
                $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $new_image_name = uniqid('phone_') . '.' . $ext;
                $upload_path = dirname(dirname(__DIR__)) . '/assets/images/' . $new_image_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $image_name = $new_image_name;
                    // Optionally delete old image if it exists and is not a placeholder
                } else {
                    $errors[] = "Failed to save the uploaded image.";
                }
            } else {
                $errors[] = "Only JPG, PNG, and WebP images are allowed.";
            }
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE phones 
                    SET brand = ?, model = ?, description = ?, price = ?, stock_quantity = ?, 
                        category_id = ?, release_date = ?, is_latest = ?, status = ?, image = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $form_data['brand'],
                    $form_data['model'],
                    $form_data['description'],
                    $form_data['price'],
                    $form_data['stock_quantity'],
                    $form_data['category_id'],
                    $form_data['release_date'],
                    $form_data['is_latest'],
                    $form_data['status'],
                    $image_name,
                    $id
                ]);
                
                // Save variants
                $variants_post = $_POST['variants'] ?? [];
                $pdo->prepare("DELETE FROM phone_variants WHERE phone_id = ?")->execute([$id]);
                $ins_var = $pdo->prepare("INSERT INTO phone_variants (phone_id, storage, color, price, stock_quantity) VALUES (?, ?, ?, ?, ?)");
                foreach ($variants_post as $v) {
                    if (isset($v['price']) && $v['price'] !== '') {
                        $v_storage = trim($v['storage'] ?? '');
                        $v_color = trim($v['color'] ?? '');
                        $v_price = (float)$v['price'];
                        $v_stock = (int)($v['stock_quantity'] ?? 0);
                        $ins_var->execute([$id, $v_storage, $v_color, $v_price, $v_stock]);
                    }
                }
                
                $_SESSION['flash_success'] = "Phone updated successfully.";
                header("Location: " . base_url('admin/phones/'));
                exit;
            } catch (PDOException $e) {
                $errors[] = "Something went wrong. Please try again.";
            }
        }
    }
}
?>

<div class="mb-6 flex items-center gap-4">
    <a href="<?php echo base_url('admin/phones/'); ?>" class="text-gray-500 hover:text-gray-900 bg-white p-2 rounded-lg border border-gray-200 shadow-sm transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Phone</h1>
        <p class="text-sm text-gray-500 mt-1">Update details for ID #<?php echo $id; ?></p>
    </div>
</div>

<?php if (!empty($errors)): ?>
<div class="bg-red-50 border-l-4 border-error p-4 mb-6 rounded-r-lg">
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

<div class="bg-card rounded-xl shadow-sm border border-gray-100 overflow-hidden max-w-4xl">
    <form action="" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Brand -->
            <div>
                <label for="brand" class="block text-sm font-medium text-gray-700 mb-1">Brand <span class="text-error">*</span></label>
                <input type="text" id="brand" name="brand" required value="<?php echo h($form_data['brand']); ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-secondary focus:border-secondary">
            </div>
            
            <!-- Model -->
            <div>
                <label for="model" class="block text-sm font-medium text-gray-700 mb-1">Model <span class="text-error">*</span></label>
                <input type="text" id="model" name="model" required value="<?php echo h($form_data['model']); ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-secondary focus:border-secondary">
            </div>
            
            <!-- Category -->
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select id="category_id" name="category_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-secondary focus:border-secondary">
                    <option value="">None</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $form_data['category_id'] == $cat['id'] ? 'selected' : ''; ?>><?php echo h($cat['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Release Date -->
            <div>
                <label for="release_date" class="block text-sm font-medium text-gray-700 mb-1">Release Date</label>
                <input type="date" id="release_date" name="release_date" value="<?php echo h($form_data['release_date']); ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-secondary focus:border-secondary">
            </div>
            
            <!-- Price -->
            <div>
                <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price (₦) <span class="text-error">*</span></label>
                <input type="number" id="price" name="price" required min="0" step="0.01" value="<?php echo h($form_data['price']); ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-secondary focus:border-secondary">
            </div>
            
            <!-- Stock -->
            <div>
                <label for="stock_quantity" class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity <span class="text-error">*</span></label>
                <input type="number" id="stock_quantity" name="stock_quantity" required min="0" step="1" value="<?php echo h($form_data['stock_quantity']); ?>" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-secondary focus:border-secondary">
            </div>
        </div>
        
        <!-- Description -->
        <div class="mb-6">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea id="description" name="description" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-secondary focus:border-secondary"><?php echo h($form_data['description']); ?></textarea>
        </div>

        <!-- Variants -->
        <div class="mb-8 border border-gray-200 rounded-xl p-6 bg-gray-50">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Product Variants</h3>
            <p class="text-sm text-gray-500 mb-4">Add different storage and color options. If variants exist, they override the base price/stock above on the product page.</p>
            
            <div id="variants-container" class="space-y-4 mb-4">
                <?php foreach ($existing_variants as $index => $v): ?>
                <div class="variant-row flex flex-wrap gap-4 items-end bg-white p-4 border border-gray-200 rounded-lg shadow-sm">
                    <div class="flex-1 min-w-[120px]">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Storage</label>
                        <input type="text" name="variants[<?php echo $index; ?>][storage]" value="<?php echo h($v['storage']); ?>" placeholder="e.g. 64GB" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="flex-1 min-w-[120px]">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Color</label>
                        <input type="text" name="variants[<?php echo $index; ?>][color]" value="<?php echo h($v['color']); ?>" placeholder="e.g. Black" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="flex-1 min-w-[100px]">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Price (₦)*</label>
                        <input type="number" name="variants[<?php echo $index; ?>][price]" value="<?php echo h($v['price']); ?>" required step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="flex-1 min-w-[80px]">
                        <label class="block text-xs font-medium text-gray-700 mb-1">Stock*</label>
                        <input type="number" name="variants[<?php echo $index; ?>][stock_quantity]" value="<?php echo h($v['stock_quantity']); ?>" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <button type="button" onclick="this.closest('.variant-row').remove()" class="text-error hover:bg-red-50 p-2 rounded-lg transition mb-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
            
            <button type="button" onclick="addVariantRow()" class="text-secondary font-medium text-sm flex items-center gap-1 hover:underline">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Add Variant Option
            </button>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Image -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Product Image</label>
                <?php if(!empty($phone['image'])): ?>
                    <div class="mb-3">
                        <img src="<?php echo base_url('assets/images/' . h($phone['image'])); ?>" alt="Current image" class="h-24 object-contain rounded border border-gray-200 p-1">
                        <p class="text-xs text-gray-500 mt-1">Current image will be kept if no new file is uploaded.</p>
                    </div>
                <?php endif; ?>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true"><path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        <div class="flex text-sm text-gray-600 justify-center">
                            <label for="image" class="relative cursor-pointer bg-white rounded-md font-medium text-secondary hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-secondary">
                                <span>Upload a new file</span>
                                <input id="image" name="image" type="file" class="sr-only" accept="image/jpeg, image/png, image/webp">
                            </label>
                        </div>
                        <p class="text-xs text-gray-500">PNG, JPG, WebP up to 5MB</p>
                    </div>
                </div>
            </div>
            
            <!-- Options -->
            <div class="flex flex-col gap-4 justify-center">
                <div class="flex items-center">
                    <input id="is_latest" name="is_latest" type="checkbox" value="1" <?php echo $form_data['is_latest'] ? 'checked' : ''; ?> class="h-5 w-5 text-secondary focus:ring-secondary border-gray-300 rounded">
                    <label for="is_latest" class="ml-3 block text-sm font-medium text-gray-700">Mark as Latest Release</label>
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="status" name="status" class="w-full md:w-2/3 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-secondary focus:border-secondary">
                        <option value="active" <?php echo $form_data['status'] === 'active' ? 'selected' : ''; ?>>Active (Visible)</option>
                        <option value="inactive" <?php echo $form_data['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive (Hidden)</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
            <a href="<?php echo base_url('admin/phones/'); ?>" class="bg-white border border-gray-300 text-gray-700 font-medium py-2 px-6 rounded-lg hover:bg-gray-50 transition">Cancel</a>
            <button type="submit" class="bg-secondary text-white font-medium py-2 px-6 rounded-lg hover:bg-blue-700 transition">Save Changes</button>
        </div>
    </form>
</div>

</div>

<script>
let variantCount = <?php echo count($existing_variants); ?>;

function addVariantRow() {
    const container = document.getElementById('variants-container');
    const row = document.createElement('div');
    row.className = 'variant-row flex flex-wrap gap-4 items-end bg-white p-4 border border-gray-200 rounded-lg shadow-sm';
    row.innerHTML = `
        <div class="flex-1 min-w-[120px]">
            <label class="block text-xs font-medium text-gray-700 mb-1">Storage</label>
            <input type="text" name="variants[${variantCount}][storage]" placeholder="e.g. 64GB" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div class="flex-1 min-w-[120px]">
            <label class="block text-xs font-medium text-gray-700 mb-1">Color</label>
            <input type="text" name="variants[${variantCount}][color]" placeholder="e.g. Black" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div class="flex-1 min-w-[100px]">
            <label class="block text-xs font-medium text-gray-700 mb-1">Price (₦)*</label>
            <input type="number" name="variants[${variantCount}][price]" required step="0.01" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div class="flex-1 min-w-[80px]">
            <label class="block text-xs font-medium text-gray-700 mb-1">Stock*</label>
            <input type="number" name="variants[${variantCount}][stock_quantity]" required value="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button type="button" onclick="this.closest('.variant-row').remove()" class="text-error hover:bg-red-50 p-2 rounded-lg transition mb-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
        </button>
    `;
    container.appendChild(row);
    variantCount++;
}
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
