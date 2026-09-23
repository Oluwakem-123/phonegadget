<?php
require_once __DIR__ . '/config/database.php';

echo "Starting Google Pixel Catalog Seeder...\n";

function build_description($specs) {
    $html = "<ul class=\"space-y-2\">\n";
    foreach ($specs as $key => $val) {
        $html .= "    <li><strong>" . htmlspecialchars($key) . ":</strong> " . htmlspecialchars($val) . "</li>\n";
    }
    $html .= "</ul>\n";
    return $html;
}

$categories = [
    'Google' => 'All Google Pixel Phones',
    'Pixel 8 Series' => 'Google Pixel 8 lineup',
    'Pixel 9 Series' => 'Google Pixel 9 lineup',
    'Pixel 10 Series' => 'Google Pixel 10 lineup'
];

$cat_ids = [];
foreach ($categories as $name => $desc) {
    $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->execute([$name]);
    $id = $stmt->fetchColumn();
    if (!$id) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute([$name, $desc]);
        $id = $pdo->lastInsertId();
    }
    $cat_ids[$name] = $id;
}

$pixel_phones = [
    // PIXEL 8 SERIES
    [
        'category_id' => $cat_ids['Pixel 8 Series'],
        'brand' => 'Google',
        'model' => 'Pixel 8',
        'price' => 550000,
        'stock_quantity' => 15,
        'release_date' => '2023-10-12',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB',
            'RAM' => '8GB',
            'Display' => '6.2-inch OLED, 120Hz',
            'Processor' => 'Google Tensor G3',
            'Rear Camera' => 'Dual: 50MP Wide, 12MP Ultrawide',
            'Front Camera' => '10.5MP',
            'Battery' => '4575 mAh, 27W wired',
            'Connectivity' => '5G, Wi-Fi 7',
            'Security' => 'Under-display Fingerprint, Face unlock',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Hazel, Rose, Obsidian'
        ]
    ],
    [
        'category_id' => $cat_ids['Pixel 8 Series'],
        'brand' => 'Google',
        'model' => 'Pixel 8 Pro',
        'price' => 750000,
        'stock_quantity' => 10,
        'release_date' => '2023-10-12',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB, 1TB',
            'RAM' => '12GB',
            'Display' => '6.7-inch LTPO OLED, 1-120Hz',
            'Processor' => 'Google Tensor G3',
            'Rear Camera' => 'Triple: 50MP Wide, 48MP Ultrawide, 48MP Telephoto',
            'Front Camera' => '10.5MP',
            'Battery' => '5050 mAh, 30W wired',
            'Connectivity' => '5G, Wi-Fi 7',
            'Security' => 'Under-display Fingerprint, Face unlock',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Bay, Porcelain, Obsidian'
        ]
    ],
    [
        'category_id' => $cat_ids['Pixel 8 Series'],
        'brand' => 'Google',
        'model' => 'Pixel 8a',
        'price' => 450000,
        'stock_quantity' => 20,
        'release_date' => '2024-05-14',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB',
            'RAM' => '8GB',
            'Display' => '6.1-inch OLED, 120Hz',
            'Processor' => 'Google Tensor G3',
            'Rear Camera' => 'Dual: 64MP Wide, 13MP Ultrawide',
            'Front Camera' => '13MP',
            'Battery' => '4492 mAh, 18W wired',
            'Connectivity' => '5G, Wi-Fi 6e',
            'Security' => 'Under-display Fingerprint, Face unlock',
            'Resistance' => 'IP67 water/dust resistance',
            'Colors' => 'Aloe, Bay, Porcelain, Obsidian'
        ]
    ],
    
    // PIXEL 9 SERIES
    [
        'category_id' => $cat_ids['Pixel 9 Series'],
        'brand' => 'Google',
        'model' => 'Pixel 9',
        'price' => 650000,
        'stock_quantity' => 15,
        'release_date' => '2024-08-22',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB',
            'RAM' => '12GB',
            'Display' => '6.3-inch OLED, 120Hz',
            'Processor' => 'Google Tensor G4',
            'Rear Camera' => 'Dual: 50MP Wide, 48MP Ultrawide',
            'Front Camera' => '10.5MP',
            'Battery' => '4700 mAh, 27W wired',
            'Connectivity' => '5G, Wi-Fi 7',
            'Security' => 'Ultrasonic Fingerprint, Face unlock',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Peony, Wintergreen, Porcelain, Obsidian'
        ]
    ],
    [
        'category_id' => $cat_ids['Pixel 9 Series'],
        'brand' => 'Google',
        'model' => 'Pixel 9 Pro',
        'price' => 850000,
        'stock_quantity' => 12,
        'release_date' => '2024-09-04',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB, 1TB',
            'RAM' => '16GB',
            'Display' => '6.3-inch LTPO OLED, 1-120Hz',
            'Processor' => 'Google Tensor G4',
            'Rear Camera' => 'Triple: 50MP Wide, 48MP Ultrawide, 48MP Telephoto',
            'Front Camera' => '42MP',
            'Battery' => '4700 mAh, 27W wired',
            'Connectivity' => '5G, Wi-Fi 7',
            'Security' => 'Ultrasonic Fingerprint, Face unlock',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Rose Quartz, Hazel, Porcelain, Obsidian'
        ]
    ],
    [
        'category_id' => $cat_ids['Pixel 9 Series'],
        'brand' => 'Google',
        'model' => 'Pixel 9 Pro XL',
        'price' => 950000,
        'stock_quantity' => 10,
        'release_date' => '2024-08-22',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB, 1TB',
            'RAM' => '16GB',
            'Display' => '6.8-inch LTPO OLED, 1-120Hz',
            'Processor' => 'Google Tensor G4',
            'Rear Camera' => 'Triple: 50MP Wide, 48MP Ultrawide, 48MP Telephoto',
            'Front Camera' => '42MP',
            'Battery' => '5060 mAh, 37W wired',
            'Connectivity' => '5G, Wi-Fi 7',
            'Security' => 'Ultrasonic Fingerprint, Face unlock',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Rose Quartz, Hazel, Porcelain, Obsidian'
        ]
    ],
    [
        'category_id' => $cat_ids['Pixel 9 Series'],
        'brand' => 'Google',
        'model' => 'Pixel 9 Pro Fold',
        'price' => 1600000,
        'stock_quantity' => 5,
        'release_date' => '2024-09-04',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '256GB, 512GB',
            'RAM' => '16GB',
            'Display' => 'Inner: 8.0-inch LTPO OLED, Cover: 6.3-inch OLED',
            'Processor' => 'Google Tensor G4',
            'Rear Camera' => 'Triple: 48MP Wide, 10.5MP Ultrawide, 10.8MP Telephoto',
            'Front Camera' => '10MP Cover, 10MP Inner',
            'Battery' => '4650 mAh, 21W wired',
            'Connectivity' => '5G, Wi-Fi 7',
            'Security' => 'Side-mounted Fingerprint, Face unlock',
            'Resistance' => 'IPX8 water resistance',
            'Colors' => 'Porcelain, Obsidian'
        ]
    ],
    [
        'category_id' => $cat_ids['Pixel 9 Series'],
        'brand' => 'Google',
        'model' => 'Pixel 9a',
        'price' => 500000,
        'stock_quantity' => 20,
        'release_date' => '2025-05-15',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB',
            'RAM' => '8GB',
            'Display' => '6.1-inch OLED, 120Hz',
            'Processor' => 'Google Tensor G4',
            'Rear Camera' => 'Dual: 48MP Wide, 13MP Ultrawide',
            'Front Camera' => '13MP',
            'Battery' => '4500 mAh, 18W wired',
            'Connectivity' => '5G, Wi-Fi 7',
            'Security' => 'Under-display Fingerprint',
            'Resistance' => 'IP67',
            'Colors' => 'Obsidian, Porcelain, Iris'
        ]
    ],

    // PIXEL 10 SERIES
    [
        'category_id' => $cat_ids['Pixel 10 Series'],
        'brand' => 'Google',
        'model' => 'Pixel 10',
        'price' => 700000,
        'stock_quantity' => 15,
        'release_date' => '2025-08-20',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB',
            'RAM' => '12GB',
            'Display' => '6.3-inch OLED, 120Hz',
            'Processor' => 'Google Tensor G5 (TSMC 3nm)',
            'Rear Camera' => 'Dual: 50MP Wide, 48MP Ultrawide',
            'Front Camera' => '12MP',
            'Battery' => '4800 mAh, 30W wired',
            'Connectivity' => '5G, Wi-Fi 7',
            'Security' => 'Ultrasonic Fingerprint, Face unlock',
            'Resistance' => 'IP68',
            'Colors' => 'Obsidian, Porcelain, Coral, Mint'
        ]
    ],
    [
        'category_id' => $cat_ids['Pixel 10 Series'],
        'brand' => 'Google',
        'model' => 'Pixel 10 Pro',
        'price' => 900000,
        'stock_quantity' => 12,
        'release_date' => '2025-08-20',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '256GB, 512GB, 1TB',
            'RAM' => '16GB',
            'Display' => '6.3-inch LTPO OLED, 1-120Hz',
            'Processor' => 'Google Tensor G5 (TSMC 3nm)',
            'Rear Camera' => 'Triple: 50MP Wide, 48MP Ultrawide, 48MP Telephoto',
            'Front Camera' => '42MP',
            'Battery' => '4800 mAh, 35W wired',
            'Connectivity' => '5G, Wi-Fi 7',
            'Security' => 'Ultrasonic Fingerprint, Face unlock',
            'Resistance' => 'IP68',
            'Colors' => 'Obsidian, Porcelain, Hazel, Rose Gold'
        ]
    ],
    [
        'category_id' => $cat_ids['Pixel 10 Series'],
        'brand' => 'Google',
        'model' => 'Pixel 10 Pro XL',
        'price' => 1000000,
        'stock_quantity' => 10,
        'release_date' => '2025-08-20',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '256GB, 512GB, 1TB',
            'RAM' => '16GB',
            'Display' => '6.8-inch LTPO OLED, 1-120Hz',
            'Processor' => 'Google Tensor G5 (TSMC 3nm)',
            'Rear Camera' => 'Triple: 50MP Wide, 48MP Ultrawide, 48MP Telephoto',
            'Front Camera' => '42MP',
            'Battery' => '5100 mAh, 45W wired',
            'Connectivity' => '5G, Wi-Fi 7',
            'Security' => 'Ultrasonic Fingerprint, Face unlock',
            'Resistance' => 'IP68',
            'Colors' => 'Obsidian, Porcelain, Hazel, Rose Gold'
        ]
    ],
    [
        'category_id' => $cat_ids['Pixel 10 Series'],
        'brand' => 'Google',
        'model' => 'Pixel 10 Pro Fold',
        'price' => 1700000,
        'stock_quantity' => 5,
        'release_date' => '2025-08-20',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '256GB, 512GB, 1TB',
            'RAM' => '16GB',
            'Display' => 'Inner: 8.0-inch LTPO OLED, Cover: 6.3-inch OLED',
            'Processor' => 'Google Tensor G5 (TSMC 3nm)',
            'Rear Camera' => 'Triple: 48MP Wide, 48MP Ultrawide, 10.8MP Telephoto',
            'Front Camera' => '10MP Cover, 10MP Inner',
            'Battery' => '4750 mAh, 30W wired',
            'Connectivity' => '5G, Wi-Fi 7',
            'Security' => 'Side-mounted Fingerprint, Face unlock',
            'Resistance' => 'IPX8',
            'Colors' => 'Obsidian, Porcelain'
        ]
    ]
];

$stmt = $pdo->prepare("INSERT INTO phones (category_id, brand, model, description, price, stock_quantity, release_date, is_latest, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())");
$v_stmt = $pdo->prepare("INSERT INTO phone_variants (phone_id, storage, color, price, stock_quantity) VALUES (?, ?, ?, ?, ?)");

$count = 0;
foreach ($pixel_phones as $phone) {
    // Check if exists
    $check = $pdo->prepare("SELECT id FROM phones WHERE brand = ? AND model = ?");
    $check->execute([$phone['brand'], $phone['model']]);
    if (!$check->fetch()) {
        $desc = build_description($phone['specs']);
        $stmt->execute([
            $phone['category_id'],
            $phone['brand'],
            $phone['model'],
            $desc,
            $phone['price'],
            $phone['stock_quantity'],
            $phone['release_date'],
            $phone['is_latest']
        ]);
        $phone_id = $pdo->lastInsertId();
        
        // Parse and insert variants
        $storages = isset($phone['specs']['Storage Options']) ? explode(',', $phone['specs']['Storage Options']) : ['Standard'];
        $colors = isset($phone['specs']['Colors']) ? explode(',', $phone['specs']['Colors']) : ['Standard'];
        
        $base_price = $phone['price'];
        
        foreach ($storages as $s_idx => $storage) {
            $storage = trim($storage);
            // Increment price by 40000 for each storage tier
            $tier_price = $base_price + ($s_idx * 40000);
            
            foreach ($colors as $c_idx => $color) {
                $color = trim($color);
                // Reduce stock artificially across variations so it looks realistic
                $v_stock = max(1, rand(2, 8));
                $v_stmt->execute([$phone_id, $storage, $color, $tier_price, $v_stock]);
            }
        }
        
        $count++;
        echo "Inserted {$phone['brand']} {$phone['model']} with variants\n";
    } else {
        echo "Skipped {$phone['brand']} {$phone['model']} (Already exists)\n";
    }
}

echo "Google Pixel Seeder finished! Inserted $count phones.\n";
