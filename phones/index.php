<?php
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/includes/header.php';

// Pagination settings
$limit = 12;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;

$user_favorites = [];
if (is_logged_in()) {
    $user_favorites = get_user_favorites($pdo, get_current_user_id());
}
$page = max(1, $page);
$offset = ($page - 1) * $limit;

// Filter variables
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$brand_filter = isset($_GET['brand']) ? trim($_GET['brand']) : '';
$latest_filter = isset($_GET['latest']) ? $_GET['latest'] == '1' : false;
$availability_filter = isset($_GET['availability']) ? trim($_GET['availability']) : '';
$pixel_series = isset($_GET['pixel_series']) ? trim($_GET['pixel_series']) : '';
$pixel_type = isset($_GET['pixel_type']) ? trim($_GET['pixel_type']) : '';
$sort_order = isset($_GET['sort']) ? trim($_GET['sort']) : 'latest'; // latest, price_asc, price_desc, name_asc

// Build the query
$where_clauses = ["status != 'discontinued'"];
$params = [];

if (!empty($search_query)) {
    $where_clauses[] = "(brand LIKE ? OR model LIKE ? OR description LIKE ?)";
    $like_q = "%$search_query%";
    $params[] = $like_q;
    $params[] = $like_q;
    $params[] = $like_q;
}

if (!empty($brand_filter)) {
    $where_clauses[] = "brand = ?";
    $params[] = $brand_filter;
}

if ($latest_filter) {
    $where_clauses[] = "is_latest = 1";
}

if ($availability_filter === 'in_stock') {
    $where_clauses[] = "stock_quantity > 0 AND status = 'available'";
} elseif ($availability_filter === 'out_of_stock') {
    $where_clauses[] = "(stock_quantity <= 0 OR status = 'out_of_stock')";
}

if (!empty($pixel_series) && $brand_filter === 'Google Pixel') {
    $where_clauses[] = "model LIKE ?";
    $params[] = $pixel_series . '%';
}

if (!empty($pixel_type) && $brand_filter === 'Google Pixel') {
    if ($pixel_type === 'Standard') {
        $where_clauses[] = "model NOT LIKE '%Pro%' AND model NOT LIKE '%a' AND model NOT LIKE '%Fold%' AND model NOT LIKE '%XL%'";
    } elseif ($pixel_type === 'Pro') {
        $where_clauses[] = "model LIKE '%Pro%' AND model NOT LIKE '%XL%' AND model NOT LIKE '%Fold%'";
    } elseif ($pixel_type === 'Pro XL') {
        $where_clauses[] = "model LIKE '%Pro XL%'";
    } elseif ($pixel_type === 'Pro Fold') {
        $where_clauses[] = "model LIKE '%Pro Fold%'";
    } elseif ($pixel_type === 'A-series') {
        $where_clauses[] = "model LIKE '%a'";
    }
}

$where_sql = implode(' AND ', $where_clauses);

// Build sorting
$order_sql = "ORDER BY release_date DESC, id DESC"; // default (latest)
if ($sort_order === 'price_asc') {
    $order_sql = "ORDER BY price ASC, id ASC";
} elseif ($sort_order === 'price_desc') {
    $order_sql = "ORDER BY price DESC, id DESC";
} elseif ($sort_order === 'name_asc') {
    $order_sql = "ORDER BY brand ASC, model ASC";
}

try {
    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) FROM phones WHERE $where_sql";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_phones = $count_stmt->fetchColumn();
    
    $total_pages = ceil($total_phones / $limit);
    
    // Fetch phones
    $sql = "SELECT * FROM phones WHERE $where_sql $order_sql LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $phones = $stmt->fetchAll();
    
    // Fetch all brands for the filter sidebar
    $brands_stmt = $pdo->query("SELECT DISTINCT brand FROM phones WHERE status != 'discontinued' ORDER BY brand ASC");
    $available_brands = $brands_stmt->fetchAll(PDO::FETCH_COLUMN);
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Helper to keep GET parameters in URLs
function build_query_string($params_to_merge = []) {
    $current_params = $_GET;
    foreach ($params_to_merge as $key => $value) {
        if ($value === null || $value === '') {
            unset($current_params[$key]);
        } else {
            $current_params[$key] = $value;
        }
    }
    return http_build_query($current_params);
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col md:flex-row gap-8">
    
    <!-- Sidebar / Filters -->
    <aside class="w-full md:w-64 flex-shrink-0">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 sticky top-24">
            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                Filters
            </h2>
            
            <form action="<?php echo base_url('phones/'); ?>" method="GET" id="filter-form">
                <!-- Keep search query if it exists -->
                <?php if(!empty($search_query)): ?>
                    <input type="hidden" name="q" value="<?php echo h($search_query); ?>">
                <?php endif; ?>
                
                <div class="mb-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Brand</h3>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        <label class="flex items-center">
                            <input type="radio" name="brand" value="" onchange="this.form.submit()" class="text-secondary focus:ring-secondary" <?php echo empty($brand_filter) ? 'checked' : ''; ?>>
                            <span class="ml-2 text-sm text-gray-600">All Brands</span>
                        </label>
                        <?php foreach($available_brands as $b): ?>
                        <label class="flex items-center">
                            <input type="radio" name="brand" value="<?php echo h($b); ?>" onchange="this.form.submit()" class="text-secondary focus:ring-secondary" <?php echo $brand_filter === $b ? 'checked' : ''; ?>>
                            <span class="ml-2 text-sm text-gray-600"><?php echo h($b); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="mb-5 border-t border-gray-100 pt-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Availability</h3>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="availability" value="" onchange="this.form.submit()" class="text-secondary focus:ring-secondary" <?php echo empty($availability_filter) ? 'checked' : ''; ?>>
                            <span class="ml-2 text-sm text-gray-600">All</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="availability" value="in_stock" onchange="this.form.submit()" class="text-secondary focus:ring-secondary" <?php echo $availability_filter === 'in_stock' ? 'checked' : ''; ?>>
                            <span class="ml-2 text-sm text-gray-600">In Stock Only</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="availability" value="out_of_stock" onchange="this.form.submit()" class="text-secondary focus:ring-secondary" <?php echo $availability_filter === 'out_of_stock' ? 'checked' : ''; ?>>
                            <span class="ml-2 text-sm text-gray-600">Out of Stock</span>
                        </label>
                    </div>
                </div>
                
                <?php if ($brand_filter === 'Google Pixel'): ?>
                <div class="mb-5 border-t border-gray-100 pt-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Pixel Series</h3>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="pixel_series" value="" onchange="this.form.submit()" class="text-secondary focus:ring-secondary" <?php echo empty($pixel_series) ? 'checked' : ''; ?>>
                            <span class="ml-2 text-sm text-gray-600">All</span>
                        </label>
                        <?php foreach(['Pixel 8', 'Pixel 9', 'Pixel 10', 'Pixel 11'] as $series): ?>
                        <label class="flex items-center">
                            <input type="radio" name="pixel_series" value="<?php echo h($series); ?>" onchange="this.form.submit()" class="text-secondary focus:ring-secondary" <?php echo $pixel_series === $series ? 'checked' : ''; ?>>
                            <span class="ml-2 text-sm text-gray-600"><?php echo h($series); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="mb-5 border-t border-gray-100 pt-5">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Pixel Type</h3>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="radio" name="pixel_type" value="" onchange="this.form.submit()" class="text-secondary focus:ring-secondary" <?php echo empty($pixel_type) ? 'checked' : ''; ?>>
                            <span class="ml-2 text-sm text-gray-600">All Types</span>
                        </label>
                        <?php foreach(['Standard', 'Pro', 'Pro XL', 'Pro Fold', 'A-series'] as $type): ?>
                        <label class="flex items-center">
                            <input type="radio" name="pixel_type" value="<?php echo h($type); ?>" onchange="this.form.submit()" class="text-secondary focus:ring-secondary" <?php echo $pixel_type === $type ? 'checked' : ''; ?>>
                            <span class="ml-2 text-sm text-gray-600"><?php echo h($type); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php else: ?>
                    <input type="hidden" name="pixel_series" value="">
                    <input type="hidden" name="pixel_type" value="">
                <?php endif; ?>
                
                <div class="border-t border-gray-100 pt-5">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="latest" value="1" onchange="this.form.submit()" class="text-secondary focus:ring-secondary rounded" <?php echo $latest_filter ? 'checked' : ''; ?>>
                        <span class="ml-2 text-sm font-medium text-gray-900">Latest Models Only</span>
                    </label>
                </div>
                
                <div class="mt-6 flex gap-2">
                    <button type="submit" class="w-full bg-secondary text-white text-sm font-semibold py-2 rounded-lg hover:bg-blue-700 transition md:hidden">Apply Filters</button>
                    <?php if(!empty($_GET)): ?>
                        <a href="<?php echo base_url('phones/'); ?>" class="w-full bg-gray-100 text-gray-700 text-center text-sm font-semibold py-2 rounded-lg hover:bg-gray-200 transition">Clear All</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </aside>
    
    <!-- Product Grid Area -->
    <div class="flex-grow">
        
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">
                    <?php 
                    if (!empty($search_query)) {
                        echo "Search Results for '" . h($search_query) . "'";
                    } elseif (!empty($brand_filter)) {
                        echo h($brand_filter) . " Phones";
                    } elseif ($latest_filter) {
                        echo "Latest Phones";
                    } else {
                        echo "All Phones";
                    }
                    ?>
                </h1>
                <p class="text-sm text-gray-500 mt-1">Showing <?php echo $total_phones; ?> result(s)</p>
            </div>
            
            <div class="flex items-center">
                <label for="sort" class="text-sm text-gray-600 mr-2 whitespace-nowrap">Sort by:</label>
                <select id="sort" class="border border-gray-300 rounded-lg text-sm py-2 px-3 focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary bg-white" onchange="window.location.href='?<?php echo build_query_string(); ?>&sort='+this.value">
                    <option value="latest" <?php echo $sort_order === 'latest' ? 'selected' : ''; ?>>Latest Arrivals</option>
                    <option value="price_asc" <?php echo $sort_order === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_desc" <?php echo $sort_order === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="name_asc" <?php echo $sort_order === 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
                </select>
            </div>
        </div>
        
        <?php if ($total_phones > 0): ?>
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-6 mb-10">
                <?php 
                foreach ($phones as $phone) {
                    require __DIR__ . '/../includes/components/phone_card.php';
                }
                ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="flex justify-center border-t border-gray-200 pt-8 pb-4">
                <nav class="flex items-center gap-1 sm:gap-2">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo build_query_string(['page' => $page - 1]); ?>" class="px-3 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="px-3 py-2 rounded-md bg-secondary text-white text-sm font-medium"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?<?php echo build_query_string(['page' => $i]); ?>" class="px-3 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?<?php echo build_query_string(['page' => $page + 1]); ?>" class="px-3 py-2 rounded-md border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
                    <?php endif; ?>
                </nav>
            </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- Empty State -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <div class="w-20 h-20 bg-gray-50 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">No phones found</h3>
                <p class="text-gray-500 mb-6">We couldn't find any phones matching your current filters.</p>
                <a href="<?php echo base_url('phones/'); ?>" class="inline-block bg-primary text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition">Clear Filters</a>
            </div>
        <?php endif; ?>
        
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
