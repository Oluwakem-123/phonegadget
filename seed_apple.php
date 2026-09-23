<?php
require_once __DIR__ . '/config/database.php';

echo "Starting Apple Catalog Seeder...\n";

// Helper function to format descriptions
function build_description($specs) {
    $html = "<ul class=\"space-y-2\">\n";
    foreach ($specs as $key => $val) {
        $html .= "    <li><strong>" . htmlspecialchars($key) . ":</strong> " . htmlspecialchars($val) . "</li>\n";
    }
    $html .= "</ul>\n";
    return $html;
}

// 1. Setup Categories
$categories = [
    'Apple' => 'All Apple iPhones',
    'iPhone 11 Series' => 'Apple iPhone 11 lineup',
    'iPhone 12 Series' => 'Apple iPhone 12 lineup',
    'iPhone 13 Series' => 'Apple iPhone 13 lineup',
    'iPhone 14 Series' => 'Apple iPhone 14 lineup',
    'iPhone 15 Series' => 'Apple iPhone 15 lineup',
    'iPhone 16 Series' => 'Apple iPhone 16 lineup',
    'iPhone 17 Series' => 'Apple iPhone 17 lineup'
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

$apple_cat_id = $cat_ids['Apple'];

$apple_phones = [
    // iPhone 11 Series
    [
        'category_id' => $cat_ids['iPhone 11 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 11',
        'price' => 350000,
        'stock_quantity' => 15,
        'release_date' => '2019-09-20',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '64GB, 128GB, 256GB',
            'RAM' => '4GB',
            'Display' => '6.1-inch Liquid Retina IPS LCD, 60Hz',
            'Processor' => 'A13 Bionic chip',
            'Rear Camera' => 'Dual 12MP (Wide, Ultrawide)',
            'Front Camera' => '12MP TrueDepth',
            'Battery' => '3110 mAh, 18W wired charging',
            'Connectivity' => '4G LTE',
            'Security' => 'Face ID',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Black, Green, Yellow, Purple, Red, White'
        ]
    ],
    [
        'category_id' => $cat_ids['iPhone 11 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 11 Pro',
        'price' => 450000,
        'stock_quantity' => 10,
        'release_date' => '2019-09-20',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '64GB, 256GB, 512GB',
            'RAM' => '4GB',
            'Display' => '5.8-inch Super Retina XDR OLED, 60Hz',
            'Processor' => 'A13 Bionic chip',
            'Rear Camera' => 'Triple 12MP (Wide, Ultrawide, Telephoto)',
            'Front Camera' => '12MP TrueDepth',
            'Battery' => '3046 mAh, 18W wired charging',
            'Connectivity' => '4G LTE',
            'Security' => 'Face ID',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Matte Space Gray, Silver, Gold, Midnight Green'
        ]
    ],
    [
        'category_id' => $cat_ids['iPhone 11 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 11 Pro Max',
        'price' => 520000,
        'stock_quantity' => 8,
        'release_date' => '2019-09-20',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '64GB, 256GB, 512GB',
            'RAM' => '4GB',
            'Display' => '6.5-inch Super Retina XDR OLED, 60Hz',
            'Processor' => 'A13 Bionic chip',
            'Rear Camera' => 'Triple 12MP (Wide, Ultrawide, Telephoto)',
            'Front Camera' => '12MP TrueDepth',
            'Battery' => '3969 mAh, 18W wired charging',
            'Connectivity' => '4G LTE',
            'Security' => 'Face ID',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Matte Space Gray, Silver, Gold, Midnight Green'
        ]
    ],

    // iPhone 12 Series
    [
        'category_id' => $cat_ids['iPhone 12 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 12 mini',
        'price' => 400000,
        'stock_quantity' => 5,
        'release_date' => '2020-11-13',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '64GB, 128GB, 256GB',
            'RAM' => '4GB',
            'Display' => '5.4-inch Super Retina XDR OLED, 60Hz',
            'Processor' => 'A14 Bionic chip',
            'Rear Camera' => 'Dual 12MP (Wide, Ultrawide)',
            'Front Camera' => '12MP TrueDepth',
            'Battery' => '2227 mAh, 20W wired, 15W MagSafe',
            'Connectivity' => '5G',
            'Security' => 'Face ID',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Black, White, Red, Green, Blue, Purple'
        ]
    ],
    [
        'category_id' => $cat_ids['iPhone 12 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 12',
        'price' => 480000,
        'stock_quantity' => 12,
        'release_date' => '2020-10-23',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '64GB, 128GB, 256GB',
            'RAM' => '4GB',
            'Display' => '6.1-inch Super Retina XDR OLED, 60Hz',
            'Processor' => 'A14 Bionic chip',
            'Rear Camera' => 'Dual 12MP (Wide, Ultrawide)',
            'Front Camera' => '12MP TrueDepth',
            'Battery' => '2815 mAh, 20W wired, 15W MagSafe',
            'Connectivity' => '5G',
            'Security' => 'Face ID',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Black, White, Red, Green, Blue, Purple'
        ]
    ],
    [
        'category_id' => $cat_ids['iPhone 12 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 12 Pro',
        'price' => 580000,
        'stock_quantity' => 10,
        'release_date' => '2020-10-23',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB',
            'RAM' => '6GB',
            'Display' => '6.1-inch Super Retina XDR OLED, 60Hz',
            'Processor' => 'A14 Bionic chip',
            'Rear Camera' => 'Triple 12MP (Wide, Ultrawide, Telephoto) + LiDAR',
            'Front Camera' => '12MP TrueDepth',
            'Battery' => '2815 mAh, 20W wired, 15W MagSafe',
            'Connectivity' => '5G',
            'Security' => 'Face ID',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Silver, Graphite, Gold, Pacific Blue'
        ]
    ],
    [
        'category_id' => $cat_ids['iPhone 12 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 12 Pro Max',
        'price' => 650000,
        'stock_quantity' => 8,
        'release_date' => '2020-11-13',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB',
            'RAM' => '6GB',
            'Display' => '6.7-inch Super Retina XDR OLED, 60Hz',
            'Processor' => 'A14 Bionic chip',
            'Rear Camera' => 'Triple 12MP (Wide, Ultrawide, Telephoto) + LiDAR',
            'Front Camera' => '12MP TrueDepth',
            'Battery' => '3687 mAh, 20W wired, 15W MagSafe',
            'Connectivity' => '5G',
            'Security' => 'Face ID',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Silver, Graphite, Gold, Pacific Blue'
        ]
    ],

    // iPhone 13 Series
    [
        'category_id' => $cat_ids['iPhone 13 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 13 mini',
        'price' => 520000,
        'stock_quantity' => 5,
        'release_date' => '2021-09-24',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB',
            'RAM' => '4GB',
            'Display' => '5.4-inch Super Retina XDR OLED, 60Hz',
            'Processor' => 'A15 Bionic chip',
            'Rear Camera' => 'Dual 12MP (Wide, Ultrawide)',
            'Front Camera' => '12MP TrueDepth',
            'Battery' => '2438 mAh, 20W wired, 15W MagSafe',
            'Connectivity' => '5G',
            'Security' => 'Face ID',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Midnight, Starlight, Blue, Pink, Green, Red'
        ]
    ],
    [
        'category_id' => $cat_ids['iPhone 13 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 13',
        'price' => 620000,
        'stock_quantity' => 15,
        'release_date' => '2021-09-24',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB',
            'RAM' => '4GB',
            'Display' => '6.1-inch Super Retina XDR OLED, 60Hz',
            'Processor' => 'A15 Bionic chip',
            'Rear Camera' => 'Dual 12MP (Wide, Ultrawide)',
            'Front Camera' => '12MP TrueDepth',
            'Battery' => '3240 mAh, 20W wired, 15W MagSafe',
            'Connectivity' => '5G',
            'Security' => 'Face ID',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Midnight, Starlight, Blue, Pink, Green, Red'
        ]
    ],
    [
        'category_id' => $cat_ids['iPhone 13 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 13 Pro',
        'price' => 750000,
        'stock_quantity' => 12,
        'release_date' => '2021-09-24',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB, 1TB',
            'RAM' => '6GB',
            'Display' => '6.1-inch Super Retina XDR OLED, 120Hz ProMotion',
            'Processor' => 'A15 Bionic chip',
            'Rear Camera' => 'Triple 12MP (Wide, Ultrawide, Telephoto) + LiDAR',
            'Front Camera' => '12MP TrueDepth',
            'Battery' => '3095 mAh, 20W wired, 15W MagSafe',
            'Connectivity' => '5G',
            'Security' => 'Face ID',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Graphite, Gold, Silver, Sierra Blue, Alpine Green'
        ]
    ],
    [
        'category_id' => $cat_ids['iPhone 13 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 13 Pro Max',
        'price' => 880000,
        'stock_quantity' => 10,
        'release_date' => '2021-09-24',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB, 1TB',
            'RAM' => '6GB',
            'Display' => '6.7-inch Super Retina XDR OLED, 120Hz ProMotion',
            'Processor' => 'A15 Bionic chip',
            'Rear Camera' => 'Triple 12MP (Wide, Ultrawide, Telephoto) + LiDAR',
            'Front Camera' => '12MP TrueDepth',
            'Battery' => '4352 mAh, 27W wired, 15W MagSafe',
            'Connectivity' => '5G',
            'Security' => 'Face ID',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Graphite, Gold, Silver, Sierra Blue, Alpine Green'
        ]
    ],

    // iPhone 14 Series
    [
        'category_id' => $cat_ids['iPhone 14 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 14',
        'price' => 750000,
        'stock_quantity' => 20,
        'release_date' => '2022-09-16',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB',
            'RAM' => '6GB',
            'Display' => '6.1-inch Super Retina XDR OLED, 60Hz',
            'Processor' => 'A15 Bionic chip',
            'Rear Camera' => 'Dual 12MP (Wide, Ultrawide)',
            'Front Camera' => '12MP TrueDepth (Autofocus)',
            'Battery' => '3279 mAh, 20W wired, 15W MagSafe',
            'Connectivity' => '5G, Emergency SOS via satellite',
            'Security' => 'Face ID',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Midnight, Starlight, Blue, Purple, Red, Yellow'
        ]
    ],
    [
        'category_id' => $cat_ids['iPhone 14 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 14 Plus',
        'price' => 850000,
        'stock_quantity' => 15,
        'release_date' => '2022-10-07',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB',
            'RAM' => '6GB',
            'Display' => '6.7-inch Super Retina XDR OLED, 60Hz',
            'Processor' => 'A15 Bionic chip',
            'Rear Camera' => 'Dual 12MP (Wide, Ultrawide)',
            'Front Camera' => '12MP TrueDepth (Autofocus)',
            'Battery' => '4325 mAh, 20W wired, 15W MagSafe',
            'Connectivity' => '5G, Emergency SOS via satellite',
            'Security' => 'Face ID',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Midnight, Starlight, Blue, Purple, Red, Yellow'
        ]
    ],
    [
        'category_id' => $cat_ids['iPhone 14 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 14 Pro',
        'price' => 1050000,
        'stock_quantity' => 12,
        'release_date' => '2022-09-16',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB, 1TB',
            'RAM' => '6GB',
            'Display' => '6.1-inch Super Retina XDR OLED, 120Hz ProMotion, Dynamic Island',
            'Processor' => 'A16 Bionic chip',
            'Rear Camera' => '48MP Wide, 12MP Ultrawide, 12MP Telephoto + LiDAR',
            'Front Camera' => '12MP TrueDepth (Autofocus)',
            'Battery' => '3200 mAh, 20W wired, 15W MagSafe',
            'Connectivity' => '5G, Emergency SOS via satellite',
            'Security' => 'Face ID',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Space Black, Silver, Gold, Deep Purple'
        ]
    ],
    [
        'category_id' => $cat_ids['iPhone 14 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 14 Pro Max',
        'price' => 1200000,
        'stock_quantity' => 10,
        'release_date' => '2022-09-16',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB, 1TB',
            'RAM' => '6GB',
            'Display' => '6.7-inch Super Retina XDR OLED, 120Hz ProMotion, Dynamic Island',
            'Processor' => 'A16 Bionic chip',
            'Rear Camera' => '48MP Wide, 12MP Ultrawide, 12MP Telephoto + LiDAR',
            'Front Camera' => '12MP TrueDepth (Autofocus)',
            'Battery' => '4323 mAh, 27W wired, 15W MagSafe',
            'Connectivity' => '5G, Emergency SOS via satellite',
            'Security' => 'Face ID',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Space Black, Silver, Gold, Deep Purple'
        ]
    ],

    // iPhone 15 Series
    [
        'category_id' => $cat_ids['iPhone 15 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 15',
        'price' => 950000,
        'stock_quantity' => 25,
        'release_date' => '2023-09-22',
        'is_latest' => 1,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB',
            'RAM' => '6GB',
            'Display' => '6.1-inch Super Retina XDR OLED, 60Hz, Dynamic Island',
            'Processor' => 'A16 Bionic chip',
            'Rear Camera' => '48MP Wide, 12MP Ultrawide',
            'Front Camera' => '12MP TrueDepth (Autofocus)',
            'Battery' => '3349 mAh, 20W USB-C, 15W MagSafe',
            'Connectivity' => '5G, USB-C 2.0',
            'Security' => 'Face ID',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Black, Blue, Green, Yellow, Pink'
        ]
    ],
    [
        'category_id' => $cat_ids['iPhone 15 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 15 Plus',
        'price' => 1100000,
        'stock_quantity' => 20,
        'release_date' => '2023-09-22',
        'is_latest' => 1,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB',
            'RAM' => '6GB',
            'Display' => '6.7-inch Super Retina XDR OLED, 60Hz, Dynamic Island',
            'Processor' => 'A16 Bionic chip',
            'Rear Camera' => '48MP Wide, 12MP Ultrawide',
            'Front Camera' => '12MP TrueDepth (Autofocus)',
            'Battery' => '4383 mAh, 20W USB-C, 15W MagSafe',
            'Connectivity' => '5G, USB-C 2.0',
            'Security' => 'Face ID',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Black, Blue, Green, Yellow, Pink'
        ]
    ],
    [
        'category_id' => $cat_ids['iPhone 15 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 15 Pro',
        'price' => 1400000,
        'stock_quantity' => 15,
        'release_date' => '2023-09-22',
        'is_latest' => 1,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB, 1TB',
            'RAM' => '8GB',
            'Display' => '6.1-inch Super Retina XDR OLED, 120Hz ProMotion, Dynamic Island',
            'Processor' => 'A17 Pro chip (3nm)',
            'Rear Camera' => '48MP Wide, 12MP Ultrawide, 12MP 3x Telephoto + LiDAR',
            'Front Camera' => '12MP TrueDepth (Autofocus)',
            'Battery' => '3274 mAh, 27W USB-C, 15W MagSafe',
            'Connectivity' => '5G, USB-C 3 (10Gbps)',
            'Security' => 'Face ID',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Natural Titanium, Blue Titanium, White Titanium, Black Titanium'
        ]
    ],
    [
        'category_id' => $cat_ids['iPhone 15 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 15 Pro Max',
        'price' => 1650000,
        'stock_quantity' => 18,
        'release_date' => '2023-09-22',
        'is_latest' => 1,
        'specs' => [
            'Storage Options' => '256GB, 512GB, 1TB',
            'RAM' => '8GB',
            'Display' => '6.7-inch Super Retina XDR OLED, 120Hz ProMotion, Dynamic Island',
            'Processor' => 'A17 Pro chip (3nm)',
            'Rear Camera' => '48MP Wide, 12MP Ultrawide, 12MP 5x Telephoto + LiDAR',
            'Front Camera' => '12MP TrueDepth (Autofocus)',
            'Battery' => '4422 mAh, 27W USB-C, 15W MagSafe',
            'Connectivity' => '5G, USB-C 3 (10Gbps)',
            'Security' => 'Face ID',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Natural Titanium, Blue Titanium, White Titanium, Black Titanium'
        ]
    ],

    // iPhone 16 Series
    [
        'category_id' => $cat_ids['iPhone 16 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 16',
        'price' => 1200000,
        'stock_quantity' => 30,
        'release_date' => '2024-09-20',
        'is_latest' => 1,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB',
            'RAM' => '8GB',
            'Display' => '6.1-inch Super Retina XDR OLED, 60Hz, Dynamic Island',
            'Processor' => 'A18 chip (3nm)',
            'Rear Camera' => '48MP Fusion (Wide), 12MP Ultrawide (Macro support)',
            'Front Camera' => '12MP TrueDepth',
            'Battery' => '3561 mAh, 25W MagSafe, 20W+ USB-C',
            'Connectivity' => '5G, USB-C 2.0, Wi-Fi 7',
            'Security' => 'Face ID',
            'Resistance' => 'IP68',
            'Colors' => 'Ultramarine, Teal, Pink, White, Black'
        ]
    ],
    [
        'category_id' => $cat_ids['iPhone 16 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 16 Plus',
        'price' => 1350000,
        'stock_quantity' => 20,
        'release_date' => '2024-09-20',
        'is_latest' => 1,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB',
            'RAM' => '8GB',
            'Display' => '6.7-inch Super Retina XDR OLED, 60Hz, Dynamic Island',
            'Processor' => 'A18 chip (3nm)',
            'Rear Camera' => '48MP Fusion (Wide), 12MP Ultrawide (Macro support)',
            'Front Camera' => '12MP TrueDepth',
            'Battery' => '4674 mAh, 25W MagSafe, 20W+ USB-C',
            'Connectivity' => '5G, USB-C 2.0, Wi-Fi 7',
            'Security' => 'Face ID',
            'Resistance' => 'IP68',
            'Colors' => 'Ultramarine, Teal, Pink, White, Black'
        ]
    ],
    [
        'category_id' => $cat_ids['iPhone 16 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 16 Pro',
        'price' => 1700000,
        'stock_quantity' => 25,
        'release_date' => '2024-09-20',
        'is_latest' => 1,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB, 1TB',
            'RAM' => '8GB',
            'Display' => '6.3-inch Super Retina XDR OLED, 120Hz ProMotion, Dynamic Island',
            'Processor' => 'A18 Pro chip (3nm)',
            'Rear Camera' => '48MP Fusion, 48MP Ultrawide, 12MP 5x Telephoto',
            'Front Camera' => '12MP TrueDepth',
            'Battery' => '3582 mAh, 25W MagSafe, 27W+ USB-C',
            'Connectivity' => '5G, USB-C 3 (10Gbps), Wi-Fi 7',
            'Security' => 'Face ID',
            'Resistance' => 'IP68',
            'Colors' => 'Black Titanium, White Titanium, Natural Titanium, Desert Titanium'
        ]
    ],
    [
        'category_id' => $cat_ids['iPhone 16 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 16 Pro Max',
        'price' => 1950000,
        'stock_quantity' => 22,
        'release_date' => '2024-09-20',
        'is_latest' => 1,
        'specs' => [
            'Storage Options' => '256GB, 512GB, 1TB',
            'RAM' => '8GB',
            'Display' => '6.9-inch Super Retina XDR OLED, 120Hz ProMotion, Dynamic Island',
            'Processor' => 'A18 Pro chip (3nm)',
            'Rear Camera' => '48MP Fusion, 48MP Ultrawide, 12MP 5x Telephoto',
            'Front Camera' => '12MP TrueDepth',
            'Battery' => '4685 mAh, 25W MagSafe, 27W+ USB-C',
            'Connectivity' => '5G, USB-C 3 (10Gbps), Wi-Fi 7',
            'Security' => 'Face ID',
            'Resistance' => 'IP68',
            'Colors' => 'Black Titanium, White Titanium, Natural Titanium, Desert Titanium'
        ]
    ],
    
    // iPhone 17 Series (Upcoming/Rumored models as requested)
    [
        'category_id' => $cat_ids['iPhone 17 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 17',
        'price' => 1400000,
        'stock_quantity' => 10,
        'release_date' => '2025-09-19',
        'is_latest' => 1,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB',
            'RAM' => '12GB',
            'Display' => '6.3-inch OLED, 120Hz ProMotion',
            'Processor' => 'A19 chip (2nm)',
            'Rear Camera' => '48MP Wide, 48MP Ultrawide',
            'Front Camera' => '24MP TrueDepth',
            'Battery' => 'Unknown, expected 25W MagSafe, USB-C',
            'Connectivity' => '5G, USB-C, Wi-Fi 7',
            'Security' => 'Face ID',
            'Resistance' => 'IP68',
            'Colors' => 'Various expected'
        ]
    ],
    [
        'category_id' => $cat_ids['iPhone 17 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 17 Pro',
        'price' => 1850000,
        'stock_quantity' => 10,
        'release_date' => '2025-09-19',
        'is_latest' => 1,
        'specs' => [
            'Storage Options' => '256GB, 512GB, 1TB, 2TB',
            'RAM' => '12GB',
            'Display' => '6.3-inch OLED, 120Hz ProMotion, Under-display Face ID',
            'Processor' => 'A19 Pro chip (2nm)',
            'Rear Camera' => 'Triple 48MP (Wide, Ultrawide, Telephoto)',
            'Front Camera' => '24MP TrueDepth',
            'Battery' => 'Unknown, USB-C, MagSafe',
            'Connectivity' => '5G, USB-C, Wi-Fi 7',
            'Security' => 'Under-display Face ID',
            'Resistance' => 'IP68',
            'Colors' => 'Titanium variants expected'
        ]
    ],
    [
        'category_id' => $cat_ids['iPhone 17 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 17 Pro Max',
        'price' => 2100000,
        'stock_quantity' => 10,
        'release_date' => '2025-09-19',
        'is_latest' => 1,
        'specs' => [
            'Storage Options' => '256GB, 512GB, 1TB, 2TB',
            'RAM' => '12GB',
            'Display' => '6.9-inch OLED, 120Hz ProMotion, Under-display Face ID',
            'Processor' => 'A19 Pro chip (2nm)',
            'Rear Camera' => 'Triple 48MP (Wide, Ultrawide, Telephoto)',
            'Front Camera' => '24MP TrueDepth',
            'Battery' => 'Unknown, USB-C, MagSafe',
            'Connectivity' => '5G, USB-C, Wi-Fi 7',
            'Security' => 'Under-display Face ID',
            'Resistance' => 'IP68',
            'Colors' => 'Titanium variants expected'
        ]
    ],
    [
        'category_id' => $cat_ids['iPhone 17 Series'],
        'brand' => 'Apple',
        'model' => 'iPhone 17 Air',
        'price' => 1950000,
        'stock_quantity' => 5,
        'release_date' => '2025-09-19',
        'is_latest' => 1,
        'specs' => [
            'Storage Options' => '256GB, 512GB',
            'RAM' => '8GB',
            'Display' => '6.6-inch OLED, 120Hz ProMotion (Ultra-thin design)',
            'Processor' => 'A19 chip (2nm)',
            'Rear Camera' => 'Single 48MP Wide',
            'Front Camera' => '24MP TrueDepth',
            'Battery' => 'Ultra-thin battery, USB-C, MagSafe',
            'Connectivity' => '5G, USB-C, Wi-Fi 7',
            'Security' => 'Face ID',
            'Resistance' => 'IP68',
            'Colors' => 'Titanium/Aluminum variants expected'
        ]
    ]
];

$stmt = $pdo->prepare("INSERT INTO phones (category_id, brand, model, description, price, stock_quantity, release_date, is_latest, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())");
$v_stmt = $pdo->prepare("INSERT INTO phone_variants (phone_id, storage, color, price, stock_quantity) VALUES (?, ?, ?, ?, ?)");

$count = 0;
foreach ($apple_phones as $phone) {
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
            // Increment price by 50000 for each storage tier
            $tier_price = $base_price + ($s_idx * 50000);
            
            foreach ($colors as $c_idx => $color) {
                $color = trim($color);
                // Reduce stock artificially across variations so it looks realistic
                $v_stock = max(1, rand(2, 10));
                $v_stmt->execute([$phone_id, $storage, $color, $tier_price, $v_stock]);
            }
        }
        
        $count++;
        echo "Inserted {$phone['brand']} {$phone['model']} with variants\n";
    } else {
        echo "Skipped {$phone['brand']} {$phone['model']} (Already exists)\n";
    }
}

echo "Apple Seeder finished! Inserted $count phones.\n";
