<?php
require_once dirname(__DIR__) . '/config/database.php';

// Must be logged in
require_login();

$user_id = get_current_user_id();

// We already have 'account/favorite_action.php' for POST requests, 
// so this page handles displaying the grid of favorites.

$favorites = get_favorite_phones($pdo, $user_id);
$total_favorites = count($favorites);

$page_title = 'My Favorites - PhoneHub';
require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
    
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Favorites</h1>
            <p class="text-sm text-gray-500 mt-1">You have <?php echo $total_favorites; ?> phone(s) saved for later.</p>
        </div>
        
        <?php if ($total_favorites > 0): ?>
        <form method="POST" action="<?php echo base_url('account/favorite_action.php'); ?>" onsubmit="return confirm('Remove all phones from your favorites?');">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="action" value="remove_all">
            <button type="submit" class="text-sm font-medium text-gray-600 hover:text-error hover:bg-red-50 px-4 py-2 rounded-lg transition-colors border border-gray-200 hover:border-red-200">
                Remove all favorites
            </button>
        </form>
        <?php endif; ?>
    </div>
    
    <?php if ($total_favorites > 0): ?>
        <!-- Favorites Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            <?php foreach ($favorites as $phone): ?>
                
                <div class="bg-card rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-300 flex flex-col relative group">
                    
                    <!-- Remove Favorite Button -->
                    <form method="POST" action="<?php echo base_url('account/favorite_action.php'); ?>" class="absolute top-2 right-2 z-10" id="form-remove-fav-<?php echo $phone['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="phone_id" value="<?php echo htmlspecialchars($phone['id']); ?>">
                        <button type="button" class="p-1.5 bg-white/90 backdrop-blur-sm rounded-full text-error hover:bg-white hover:text-red-700 shadow-sm focus:outline-none transition-colors" title="Remove from favorites" onclick="openModal('Remove this phone from your favorites?', 'Remove', 'Cancel', true, () => document.getElementById('form-remove-fav-<?php echo $phone['id']; ?>').submit())" aria-label="Remove <?php echo htmlspecialchars($phone['model']); ?> from favorites">
                            <svg class="w-5 h-5 fill-current" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        </button>
                    </form>
                    
                    <!-- Product Image -->
                    <a href="<?php echo base_url('phones/details.php?id=' . urlencode($phone['id'])); ?>" class="block p-4 border-b border-gray-50 bg-white">
                        <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden flex items-center justify-center min-h-[160px]">
                            <?php if(!empty($phone['image'])): ?>
                                <img src="<?php echo base_url('assets/images/' . htmlspecialchars($phone['image'])); ?>" alt="<?php echo htmlspecialchars($phone['model']); ?>" class="w-full h-full object-contain">
                            <?php else: ?>
                                <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            <?php endif; ?>
                        </div>
                    </a>
                    
                    <!-- Product Info -->
                    <div class="p-4 flex-grow flex flex-col bg-white">
                        <div class="text-[11px] font-medium text-gray-500 uppercase tracking-wider mb-1"><?php echo htmlspecialchars($phone['brand']); ?></div>
                        <h3 class="font-semibold text-gray-900 leading-tight mb-2 text-sm sm:text-base line-clamp-2">
                            <a href="<?php echo base_url('phones/details.php?id=' . urlencode($phone['id'])); ?>" class="hover:text-secondary transition-colors"><?php echo htmlspecialchars($phone['model']); ?></a>
                        </h3>
                        
                        <!-- Stock Status -->
                        <?php 
                            $is_available = true;
                            $stock_class = 'text-success';
                            $stock_text = 'In Stock';
                            if ($phone['status'] === 'out_of_stock' || $phone['stock_quantity'] <= 0 || $phone['status'] === 'inactive') {
                                $is_available = false;
                                $stock_class = 'text-error';
                                $stock_text = 'Currently unavailable';
                            } elseif ($phone['stock_quantity'] < 10) {
                                $stock_class = 'text-accent';
                                $stock_text = 'Low Stock';
                            }
                        ?>
                        <div class="mb-3 text-xs font-medium <?php echo $stock_class; ?>">
                            <?php echo $stock_text; ?>
                        </div>
                        
                        <div class="mt-auto flex flex-col gap-3">
                            <span class="font-bold text-gray-900 text-lg">₦<?php echo number_format($phone['price']); ?></span>
                            
                            <?php if ($is_available): ?>
                            <form method="POST" action="<?php echo base_url('cart/action.php'); ?>">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="phone_id" value="<?php echo htmlspecialchars($phone['id']); ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="w-full bg-secondary text-white text-sm font-semibold py-2 px-4 rounded-lg shadow-sm hover:bg-blue-700 transition flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    Add to Cart
                                </button>
                            </form>
                            <?php else: ?>
                            <button disabled class="w-full bg-gray-100 text-gray-400 text-sm font-semibold py-2 px-4 rounded-lg cursor-not-allowed text-center">
                                Out of Stock
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
            <?php endforeach; ?>
        </div>
        
    <?php else: ?>
        <!-- Empty State -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 md:p-20 text-center max-w-2xl mx-auto">
            <div class="w-24 h-24 bg-red-50 text-error rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-3">No favorite phones yet</h2>
            <p class="text-gray-500 mb-8 max-w-md mx-auto">Save phones you like and they'll appear here.</p>
            <a href="<?php echo base_url('phones/'); ?>" class="inline-block bg-primary text-white font-medium px-8 py-3 rounded-xl shadow-md hover:bg-gray-800 transition transform hover:-translate-y-0.5">
                Browse Phones
            </a>
        </div>
    <?php endif; ?>
    
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
