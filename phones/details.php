<?php
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/header.php';

$phone_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$phone = null;

if ($phone_id > 0) {
    try {
        $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM phones p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.status != 'discontinued'");
        $stmt->execute([$phone_id]);
        $phone = $stmt->fetch();
        
        $variants = [];
        if ($phone) {
            $v_stmt = $pdo->prepare("SELECT * FROM phone_variants WHERE phone_id = ? ORDER BY price ASC");
            $v_stmt->execute([$phone['id']]);
            $variants = $v_stmt->fetchAll();
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    
    <!-- Breadcrumbs -->
    <nav class="flex text-sm text-gray-500 mb-8" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="<?php echo base_url(); ?>" class="hover:text-secondary">Home</a>
            </li>
            <li>
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    <a href="<?php echo base_url('phones/'); ?>" class="hover:text-secondary">Phones</a>
                </div>
            </li>
            <?php if ($phone): ?>
            <li>
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    <a href="<?php echo base_url('phones/?brand=' . urlencode($phone['brand'])); ?>" class="hover:text-secondary"><?php echo h($phone['brand']); ?></a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-4 h-4 text-gray-400 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    <span class="text-gray-800 font-medium truncate max-w-[150px] sm:max-w-xs"><?php echo h($phone['model']); ?></span>
                </div>
            </li>
            <?php endif; ?>
        </ol>
    </nav>

    <?php if (!$phone): ?>
        <!-- Empty State for Invalid ID -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 md:p-20 text-center max-w-2xl mx-auto mt-8">
            <div class="w-24 h-24 bg-red-50 text-error rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Phone Not Found</h1>
            <p class="text-lg text-gray-500 mb-8">The smartphone you are looking for does not exist or is no longer available.</p>
            <a href="<?php echo base_url('phones/'); ?>" class="inline-block bg-primary text-white font-semibold px-8 py-3.5 rounded-xl shadow-lg hover:bg-gray-800 transition transform hover:-translate-y-0.5">Browse All Phones</a>
        </div>
    <?php else: ?>
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-0">
                
                <!-- Product Image Gallery -->
                <div class="p-8 md:p-12 flex items-center justify-center bg-gray-50 border-b md:border-b-0 md:border-r border-gray-100 relative min-h-[300px]">
                    <?php if($phone['is_latest']): ?>
                        <span class="absolute top-6 left-6 bg-accent text-white text-xs font-bold px-3 py-1.5 rounded-lg uppercase tracking-wider shadow-sm">New Release</span>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?php echo base_url('account/favorite_action.php'); ?>" class="absolute top-6 right-6" id="form-detail-fav-<?php echo $phone['id']; ?>" data-custom-submit="true">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="action" value="toggle">
                        <input type="hidden" name="phone_id" value="<?php echo htmlspecialchars($phone['id']); ?>">
                        <?php 
                            $is_favorited = false;
                            $is_logged_in = is_logged_in();
                            if ($is_logged_in && isset($pdo)) {
                                $is_favorited = is_favorite($pdo, get_current_user_id(), $phone['id']);
                            }
                            
                            if (!$is_logged_in) {
                                $onclick = "openModal('Please log in to save phones to your favorites.', 'Log In', 'Cancel', false, () => window.location.href = '" . base_url('auth/login.php') . "')";
                            } elseif ($is_favorited) {
                                $onclick = "openModal('Remove this phone from your favorites?', 'Remove', 'Cancel', true, () => document.getElementById('form-detail-fav-{$phone['id']}').submit())";
                            } else {
                                $onclick = "document.getElementById('form-detail-fav-{$phone['id']}').submit()";
                            }
                        ?>
                        <button type="button" class="p-2 bg-white rounded-full <?php echo $is_favorited ? 'text-error' : 'text-gray-400 hover:text-error'; ?> hover:bg-gray-100 shadow-sm focus:outline-none transition-colors" title="<?php echo $is_favorited ? 'Remove from favorites' : 'Add to favorites'; ?>" aria-label="Toggle Favorite" onclick="<?php echo $onclick; ?>">
                            <svg class="w-6 h-6 <?php echo $is_favorited ? 'fill-current' : ''; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        </button>
                    </form>
                    
                    <?php if(!empty($phone['image'])): ?>
                        <img src="<?php echo base_url('assets/images/' . h($phone['image'])); ?>" alt="<?php echo h($phone['model']); ?>" class="max-w-full max-h-[400px] object-contain">
                    <?php else: ?>
                        <svg class="w-32 h-32 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                    <?php endif; ?>
                </div>
                
                <!-- Product Information -->
                <div class="p-8 md:p-12 flex flex-col">
                    <div class="mb-2">
                        <a href="<?php echo base_url('phones/?brand=' . urlencode($phone['brand'])); ?>" class="text-secondary font-semibold uppercase tracking-wider text-sm hover:underline"><?php echo h($phone['brand']); ?></a>
                    </div>
                    
                    <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 leading-tight mb-4"><?php echo h($phone['model']); ?></h1>
                    
                    <div class="flex items-center gap-4 mb-6 pb-6 border-b border-gray-100">
                        <span class="text-3xl font-bold text-gray-900" id="display-price">₦<?php echo number_format(empty($variants) ? $phone['price'] : $variants[0]['price']); ?></span>
                        
                        <?php 
                            $initial_stock = empty($variants) ? $phone['stock_quantity'] : $variants[0]['stock_quantity'];
                            $initial_status = $phone['status']; // If phone is globally out_of_stock
                            $stock_class = 'bg-green-100 text-green-800 border-green-200';
                            $stock_text = 'In Stock';
                            $can_add_to_cart = true;
                            
                            if ($initial_status === 'out_of_stock' || $initial_stock <= 0) {
                                $stock_class = 'bg-red-100 text-red-800 border-red-200';
                                $stock_text = 'Out of Stock';
                                $can_add_to_cart = false;
                            } elseif ($initial_stock < 10) {
                                $stock_class = 'bg-yellow-100 text-yellow-800 border-yellow-200';
                                $stock_text = 'Only ' . $initial_stock . ' Left';
                            }
                        ?>
                        <span class="px-3 py-1 rounded-full text-xs font-bold border <?php echo $stock_class; ?>" id="display-stock-badge">
                            <?php echo $stock_text; ?>
                        </span>
                    </div>
                    
                    <div class="prose prose-sm text-gray-600 mb-8 flex-grow">
                        <p class="leading-relaxed"><?php echo nl2br(h($phone['description'])); ?></p>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-8 text-sm text-gray-600 bg-gray-50 p-4 rounded-xl">
                        <div>
                            <span class="block text-gray-400 text-xs uppercase tracking-wider mb-1">Release Date</span>
                            <span class="font-medium text-gray-900"><?php echo !empty($phone['release_date']) ? date('M d, Y', strtotime($phone['release_date'])) : 'Unknown'; ?></span>
                        </div>
                        <div>
                            <span class="block text-gray-400 text-xs uppercase tracking-wider mb-1">Category</span>
                            <span class="font-medium text-gray-900"><?php echo h($phone['category_name'] ?? 'Smartphone'); ?></span>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="mt-auto pt-4 flex flex-col gap-4">
                        <?php if (!empty($variants)): ?>
                        <div class="flex flex-col gap-2">
                            <label for="variant_id" class="text-sm font-semibold text-gray-700">Select Option</label>
                            <select id="variant_id" name="variant_id" form="add-to-cart-form" class="w-full border-gray-300 rounded-xl shadow-sm focus:border-secondary focus:ring focus:ring-secondary focus:ring-opacity-50 py-3" onchange="updateVariant(this)">
                                <?php foreach ($variants as $v): ?>
                                    <option value="<?php echo $v['id']; ?>" data-price="<?php echo number_format($v['price']); ?>" data-stock="<?php echo $v['stock_quantity']; ?>">
                                        <?php echo h(trim($v['storage'] . ' ' . $v['color'])); ?> - ₦<?php echo number_format($v['price']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <?php if ($can_add_to_cart): ?>
                            <form method="POST" action="<?php echo base_url('cart/action.php'); ?>" class="flex-grow flex gap-3" id="add-to-cart-form">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="phone_id" value="<?php echo $phone['id']; ?>">
                                <div class="flex items-center border border-gray-300 rounded-xl bg-white w-32 flex-shrink-0 overflow-hidden">
                                    <button type="button" class="px-4 py-3 text-gray-600 hover:bg-gray-100 hover:text-gray-900 focus:outline-none transition" onclick="const input = document.getElementById('qty_input'); if(input.value > 1) input.value--">-</button>
                                    <input type="number" id="qty_input" name="quantity" value="1" min="1" max="<?php echo $initial_stock; ?>" class="w-full text-center font-semibold text-gray-900 bg-transparent border-none focus:ring-0 p-0" readonly>
                                    <button type="button" class="px-4 py-3 text-gray-600 hover:bg-gray-100 hover:text-gray-900 focus:outline-none transition" onclick="const input = document.getElementById('qty_input'); if(input.value < input.max) input.value++">+</button>
                                </div>
                                <button type="submit" id="add-to-cart-btn" class="flex-1 bg-secondary text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-blue-500/30 hover:bg-blue-700 transition flex items-center justify-center gap-2 transform hover:-translate-y-0.5">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    Add to Cart
                                </button>
                            </form>
                            <?php else: ?>
                            <button disabled class="w-full bg-gray-200 text-gray-500 font-bold py-3.5 px-6 rounded-xl cursor-not-allowed flex items-center justify-center gap-2" id="unavailable-btn">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Currently Unavailable
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
        
    <?php endif; ?>

</div>

<script>
function updateVariant(select) {
    const option = select.options[select.selectedIndex];
    const price = option.getAttribute('data-price');
    const stock = parseInt(option.getAttribute('data-stock'));
    const isGlobalOutOfStock = <?php echo ($phone['status'] === 'out_of_stock' ? 'true' : 'false'); ?>;
    
    // Update price
    document.getElementById('display-price').innerText = '₦' + price;
    
    // Update stock and buttons
    const badge = document.getElementById('display-stock-badge');
    const qtyInput = document.getElementById('qty_input');
    const addBtn = document.getElementById('add-to-cart-btn');
    const unavBtn = document.getElementById('unavailable-btn');
    
    if (qtyInput) {
        qtyInput.max = stock;
        if (parseInt(qtyInput.value) > stock && stock > 0) {
            qtyInput.value = stock;
        }
    }
    
    if (isGlobalOutOfStock || stock <= 0) {
        badge.className = 'px-3 py-1 rounded-full text-xs font-bold border bg-red-100 text-red-800 border-red-200';
        badge.innerText = 'Out of Stock';
        if (addBtn) addBtn.disabled = true;
    } else {
        if (addBtn) addBtn.disabled = false;
        if (stock < 10) {
            badge.className = 'px-3 py-1 rounded-full text-xs font-bold border bg-yellow-100 text-yellow-800 border-yellow-200';
            badge.innerText = 'Only ' + stock + ' Left';
        } else {
            badge.className = 'px-3 py-1 rounded-full text-xs font-bold border bg-green-100 text-green-800 border-green-200';
            badge.innerText = 'In Stock';
        }
    }
}
</script>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
