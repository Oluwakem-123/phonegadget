<?php
// Initialize database connection and functions
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$user_favorites = [];
if (is_logged_in()) {
    $user_favorites = get_user_favorites($pdo, get_current_user_id());
}
?>

<!-- Mobile Search Bar (Visible only on mobile/tablet) -->
<div class="md:hidden px-4 py-3 bg-white border-b border-gray-200 sticky top-16 z-40">
    <form action="<?php echo base_url('phones/'); ?>" method="GET" class="relative text-gray-900">
        <input type="text" name="q" value="<?php echo isset($_GET['q']) ? h($_GET['q']) : ''; ?>" placeholder="Search phones, brands..." class="w-full bg-gray-100 rounded-full py-2.5 pl-4 pr-10 focus:outline-none focus:ring-2 focus:ring-secondary text-sm">
        <button type="submit" class="absolute right-3 top-2.5 text-gray-500 hover:text-secondary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </button>
    </form>
</div>

<!-- Hero Section -->
<div class="bg-primary text-white rounded-none md:rounded-2xl mt-0 md:mt-6 mx-0 md:mx-4 overflow-hidden relative">
    <div class="absolute inset-0 bg-gradient-to-r from-gray-900 to-gray-800 opacity-90"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-6 py-12 md:py-20 text-center md:text-left flex flex-col md:flex-row md:items-center justify-between">
        <div class="md:w-1/2">
            <span class="inline-block py-1 px-3 rounded-full bg-secondary/20 text-blue-200 text-xs font-semibold tracking-wider mb-4 border border-secondary/30">#1 PHONE MARKETPLACE</span>
            <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight mb-4 leading-tight">Find Your Next Phone</h1>
            <p class="text-base md:text-xl text-gray-300 mb-8 max-w-lg mx-auto md:mx-0">Discover the latest smartphones at great prices. Shop top brands with secure payments and fast delivery.</p>
            <a href="<?php echo base_url('phones/'); ?>" class="inline-block bg-secondary text-white font-semibold px-8 py-3.5 rounded-lg shadow-lg hover:bg-blue-700 transition duration-200 transform hover:-translate-y-0.5">Shop Phones</a>
        </div>
        <div class="hidden md:flex md:w-1/2 justify-end mt-10 md:mt-0 relative group perspective-1000 pl-4 lg:pl-12">
            <!-- Premium Ambient Glow -->
            <div class="absolute inset-0 bg-gradient-to-r from-secondary/40 to-purple-500/40 blur-[80px] rounded-full group-hover:blur-[100px] group-hover:scale-110 transition-all duration-1000 z-0"></div>
            
            <!-- Premium Image Container / Crop Window -->
            <div class="relative z-10 w-full h-[350px] lg:h-[450px] xl:h-[500px] rounded-[2.5rem] overflow-hidden border border-white/10 shadow-[0_20px_60px_-15px_rgba(0,0,0,0.8)] backdrop-blur-sm transform transition-all duration-700 ease-out group-hover:-translate-y-2 group-hover:rotate-1 group-hover:shadow-[0_30px_80px_-20px_rgba(37,99,235,0.6)]">
                
                <!-- Internal Gradient Overlay for Premium Look -->
                <div class="absolute inset-0 bg-gradient-to-tr from-black/20 via-transparent to-white/10 z-20 pointer-events-none rounded-[2.5rem]"></div>
                
                <!-- The Image (CSS Cropped) -->
                <img src="https://www.apple.com/v/home/images/iphone-family/a/hero_iphone_family__be5jkzxszb1e_large.jpg" 
                     alt="Premium Smartphones" 
                     class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-auto h-[120%] min-w-[150%] object-cover group-hover:scale-[1.03] transition-transform duration-1000 ease-out z-10">
            </div>
        </div>
    </div>
</div>

<!-- Notification / News Section -->
<div class="mt-6 mx-4 md:mx-6 bg-blue-50 border border-blue-100 rounded-lg p-4 flex items-start gap-3 shadow-sm">
    <div class="mt-0.5 text-secondary flex-shrink-0">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>
    </div>
    <div>
        <h4 class="text-sm font-semibold text-gray-900">Weekend Special Deal!</h4>
        <p class="text-xs text-gray-600 mt-1">Get up to 15% off on selected Samsung and Apple devices this weekend only. <a href="#" class="text-secondary font-medium hover:underline">View Deals</a></p>
    </div>
</div>

<!-- Popular Brands (Horizontal Scroll) -->
<div class="py-8">
    <div class="px-4 sm:px-6 lg:px-8 mb-4">
        <h2 class="text-lg font-bold text-gray-900">Popular Brands</h2>
    </div>
    <div class="flex overflow-x-auto hide-scrollbar px-4 sm:px-6 lg:px-8 gap-4 pb-4">
        <?php 
        try {
            // Fetch distinct brands that have active phones
            $stmt = $pdo->query("SELECT DISTINCT brand FROM phones WHERE status != 'discontinued' ORDER BY brand ASC LIMIT 10");
            $brands = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (count($brands) > 0) {
                foreach($brands as $brand): 
                ?>
                <a href="<?php echo base_url('phones/?brand=' . urlencode($brand)); ?>" class="flex-shrink-0 flex items-center justify-center bg-white border border-gray-200 rounded-xl px-6 py-3 shadow-sm hover:border-secondary hover:shadow-md transition-all">
                    <span class="font-semibold text-gray-700"><?php echo htmlspecialchars($brand); ?></span>
                </a>
                <?php 
                endforeach; 
            } else {
                echo '<span class="text-sm text-gray-500">No brands found.</span>';
            }
        } catch (PDOException $e) {
            echo '<span class="text-sm text-error">Failed to load brands.</span>';
        }
        ?>
    </div>
</div>

<!-- Latest Phones / New Releases Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl md:text-2xl font-bold text-gray-900">New Releases</h2>
        <a href="<?php echo base_url('phones/?latest=1'); ?>" class="text-secondary text-sm font-medium hover:underline flex items-center">
            View All
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
        </a>
    </div>

    <!-- Responsive Product Grid: 2 on mobile, 3 on tablet, 4 on desktop -->
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-6">
        
        <?php 
        try {
            // Fetch latest phones from DB
            $stmt = $pdo->query("SELECT * FROM phones WHERE status != 'discontinued' ORDER BY release_date DESC, id DESC LIMIT 8");
            $latest_phones = $stmt->fetchAll();
            
            if (count($latest_phones) > 0) {
                foreach ($latest_phones as $phone) {
                    require __DIR__ . '/includes/components/phone_card.php';
                }
            } else {
                echo '<div class="col-span-full py-12 text-center text-gray-500">No phones available at the moment.</div>';
            }
        } catch (PDOException $e) {
            echo '<div class="col-span-full py-4 text-center text-error">Failed to load phones.</div>';
        }
        ?>

    </div>
</div>

<!-- Why Shop With Us Section -->
<div class="bg-white py-12 border-y border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-2xl font-bold text-gray-900">Why Shop With PhoneHub?</h2>
            <p class="text-gray-500 mt-2">We provide the best shopping experience for your tech needs</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 text-center">
            <div class="p-4 flex flex-col items-center">
                <div class="w-14 h-14 bg-blue-50 text-secondary rounded-2xl flex items-center justify-center mb-4 transform -rotate-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4V3H5v9h4v4z"></path></svg>
                </div>
                <h3 class="font-bold text-gray-900 mb-1">Authentic Devices</h3>
                <p class="text-gray-500 text-sm">100% genuine products with manufacturer warranty</p>
            </div>
            <div class="p-4 flex flex-col items-center">
                <div class="w-14 h-14 bg-green-50 text-success rounded-2xl flex items-center justify-center mb-4 transform rotate-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <h3 class="font-bold text-gray-900 mb-1">Secure Payments</h3>
                <p class="text-gray-500 text-sm">Safe and encrypted checkout process</p>
            </div>
            <div class="p-4 flex flex-col items-center">
                <div class="w-14 h-14 bg-yellow-50 text-accent rounded-2xl flex items-center justify-center mb-4 transform -rotate-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <h3 class="font-bold text-gray-900 mb-1">Fast Delivery</h3>
                <p class="text-gray-500 text-sm">Nationwide shipping within 24-48 hours</p>
            </div>
            <div class="p-4 flex flex-col items-center">
                <div class="w-14 h-14 bg-blue-50 text-secondary rounded-2xl flex items-center justify-center mb-4 transform rotate-3">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <h3 class="font-bold text-gray-900 mb-1">24/7 Support</h3>
                <p class="text-gray-500 text-sm">Always here to help with your inquiries</p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
