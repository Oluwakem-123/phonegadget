<?php
// Ensure $phone is available when included
if (!isset($phone)) {
    return;
}
?>
<!-- Reusable Product Card -->
<div class="product-card bg-card rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-all duration-300 flex flex-col group relative">
    
    <!-- Badges -->
    <div class="absolute top-2 left-2 z-10 flex flex-col gap-1">
        <?php if($phone['is_latest']): ?>
        <span class="bg-accent text-white text-[10px] font-bold px-2 py-1 rounded-md uppercase tracking-wide shadow-sm">New</span>
        <?php endif; ?>
    </div>

    <!-- Favorite Button -->
    <form method="POST" action="<?php echo base_url('account/favorite_action.php'); ?>" class="absolute top-2 right-2 z-10" id="form-card-fav-<?php echo $phone['id']; ?>" data-custom-submit="true">
        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
        <input type="hidden" name="action" value="toggle">
        <input type="hidden" name="phone_id" value="<?php echo htmlspecialchars($phone['id']); ?>">
        <?php 
            $is_favorited = false;
            $is_logged_in = is_logged_in();
            if ($is_logged_in && isset($user_favorites)) {
                $is_favorited = in_array($phone['id'], $user_favorites);
            }
            
            // Build the appropriate onclick handler
            if (!$is_logged_in) {
                $onclick = "openModal('Please log in to save phones to your favorites.', 'Log In', 'Cancel', false, () => window.location.href = '" . base_url('auth/login.php') . "')";
            } elseif ($is_favorited) {
                $onclick = "openModal('Remove this phone from your favorites?', 'Remove', 'Cancel', true, () => document.getElementById('form-card-fav-{$phone['id']}').submit())";
            } else {
                $onclick = "document.getElementById('form-card-fav-{$phone['id']}').submit()";
            }
        ?>
        <button type="button" class="p-1.5 bg-white/80 backdrop-blur-sm rounded-full <?php echo $is_favorited ? 'text-error' : 'text-gray-400 hover:text-error'; ?> hover:bg-white shadow-sm focus:outline-none transition-colors" aria-label="<?php echo $is_favorited ? 'Remove ' . htmlspecialchars($phone['model']) . ' from favorites' : 'Add ' . htmlspecialchars($phone['model']) . ' to favorites'; ?>" title="<?php echo $is_favorited ? 'Remove from favorites' : 'Add to favorites'; ?>" onclick="<?php echo $onclick; ?>">
            <svg class="w-5 h-5 <?php echo $is_favorited ? 'fill-current' : ''; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
        </button>
    </form>

    <!-- Product Image -->
    <a href="<?php echo base_url('phones/details.php?id=' . urlencode($phone['id'])); ?>" class="block product-image-container p-4 border-b border-gray-50">
        <?php if(!empty($phone['image'])): ?>
            <img src="<?php echo base_url('assets/images/' . htmlspecialchars($phone['image'])); ?>" alt="<?php echo htmlspecialchars($phone['model']); ?>">
        <?php else: ?>
            <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
        <?php endif; ?>
    </a>
    
    <!-- Product Info -->
    <div class="p-3 sm:p-4 flex-grow flex flex-col">
        <div class="text-[11px] font-medium text-gray-500 uppercase tracking-wider mb-1"><?php echo htmlspecialchars($phone['brand']); ?></div>
        <h3 class="font-semibold text-gray-900 leading-tight mb-1 text-sm sm:text-base line-clamp-2">
            <a href="<?php echo base_url('phones/details.php?id=' . urlencode($phone['id'])); ?>" class="hover:text-secondary transition-colors"><?php echo htmlspecialchars($phone['model']); ?></a>
        </h3>
        
        <!-- Stock Status -->
        <?php 
            $stock_class = 'text-success';
            $stock_text = 'In Stock';
            if ($phone['status'] === 'out_of_stock' || $phone['stock_quantity'] <= 0) {
                $stock_class = 'text-error';
                $stock_text = 'Out of Stock';
            } elseif ($phone['stock_quantity'] < 10) {
                $stock_class = 'text-accent';
                $stock_text = 'Low Stock';
            }
        ?>
        <div class="mb-3 text-[11px] font-medium <?php echo $stock_class; ?>">
            <?php echo $stock_text; ?>
        </div>
        
        <div class="mt-auto pt-2 border-t border-gray-100 flex items-center justify-between gap-1">
            <span class="font-bold text-gray-900 text-sm sm:text-lg">₦<?php echo number_format($phone['price']); ?></span>
            <a href="<?php echo base_url('phones/details.php?id=' . urlencode($phone['id'])); ?>" class="bg-gray-100 text-primary p-2 rounded-lg hover:bg-secondary hover:text-white transition-colors focus:outline-none flex-shrink-0" aria-label="View Details">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
        </div>
    </div>
</div>
