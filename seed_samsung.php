<?php
require_once __DIR__ . '/config/database.php';

echo "Starting Samsung Catalog Seeder...\n";

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
    'Samsung' => 'All Samsung Galaxy phones',
    'Galaxy S20 Series' => 'Samsung Galaxy S20 lineup',
    'Galaxy S21 Series' => 'Samsung Galaxy S21 lineup',
    'Galaxy S22 Series' => 'Samsung Galaxy S22 lineup',
    'Galaxy S23 Series' => 'Samsung Galaxy S23 lineup',
    'Galaxy S24 Series' => 'Samsung Galaxy S24 lineup',
    'Galaxy S25 Series' => 'Samsung Galaxy S25 lineup',
    'Galaxy S26 Series' => 'Samsung Galaxy S26 lineup'
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

$samsung_phones = [
    // S20 Series
    [
        'category_id' => $cat_ids['Galaxy S20 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S20',
        'price' => 300000,
        'stock_quantity' => 10,
        'release_date' => '2020-03-06',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB',
            'RAM' => '8GB',
            'Display' => '6.2-inch Dynamic AMOLED 2X, 120Hz',
            'Processor' => 'Exynos 990 / Snapdragon 865',
            'Rear Camera' => '12MP Wide, 64MP Telephoto, 12MP Ultrawide',
            'Front Camera' => '10MP',
            'Battery' => '4000 mAh, 25W wired, 15W wireless',
            'Connectivity' => '4G/5G',
            'Security' => 'Ultrasonic Fingerprint',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Cosmic Grey, Cloud Blue, Cloud Pink, Cloud White'
        ]
    ],
    [
        'category_id' => $cat_ids['Galaxy S20 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S20+',
        'price' => 380000,
        'stock_quantity' => 8,
        'release_date' => '2020-03-06',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB',
            'RAM' => '8GB, 12GB',
            'Display' => '6.7-inch Dynamic AMOLED 2X, 120Hz',
            'Processor' => 'Exynos 990 / Snapdragon 865',
            'Rear Camera' => '12MP Wide, 64MP Telephoto, 12MP Ultrawide, DepthVision',
            'Front Camera' => '10MP',
            'Battery' => '4500 mAh, 25W wired, 15W wireless',
            'Connectivity' => '4G/5G',
            'Security' => 'Ultrasonic Fingerprint',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Cosmic Grey, Cosmic Black, Cloud Blue'
        ]
    ],
    [
        'category_id' => $cat_ids['Galaxy S20 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S20 Ultra',
        'price' => 450000,
        'stock_quantity' => 5,
        'release_date' => '2020-03-06',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB',
            'RAM' => '12GB, 16GB',
            'Display' => '6.9-inch Dynamic AMOLED 2X, 120Hz',
            'Processor' => 'Exynos 990 / Snapdragon 865',
            'Rear Camera' => '108MP Wide, 48MP Periscope Telephoto, 12MP Ultrawide, TOF 3D',
            'Front Camera' => '40MP',
            'Battery' => '5000 mAh, 45W wired, 15W wireless',
            'Connectivity' => '5G',
            'Security' => 'Ultrasonic Fingerprint',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Cosmic Grey, Cosmic Black'
        ]
    ],
    [
        'category_id' => $cat_ids['Galaxy S20 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S20 FE',
        'price' => 280000,
        'stock_quantity' => 15,
        'release_date' => '2020-10-02',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB',
            'RAM' => '6GB, 8GB',
            'Display' => '6.5-inch Super AMOLED, 120Hz',
            'Processor' => 'Exynos 990 / Snapdragon 865',
            'Rear Camera' => '12MP Wide, 8MP Telephoto, 12MP Ultrawide',
            'Front Camera' => '32MP',
            'Battery' => '4500 mAh, 25W wired, 15W wireless',
            'Connectivity' => '4G/5G',
            'Security' => 'Optical Fingerprint',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Cloud Lavender, Cloud Mint, Cloud Navy, Cloud White, Cloud Red, Cloud Orange'
        ]
    ],

    // S21 Series
    [
        'category_id' => $cat_ids['Galaxy S21 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S21',
        'price' => 400000,
        'stock_quantity' => 12,
        'release_date' => '2021-01-29',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB',
            'RAM' => '8GB',
            'Display' => '6.2-inch Dynamic AMOLED 2X, 120Hz',
            'Processor' => 'Exynos 2100 / Snapdragon 888',
            'Rear Camera' => '12MP Wide, 64MP Telephoto, 12MP Ultrawide',
            'Front Camera' => '10MP',
            'Battery' => '4000 mAh, 25W wired, 15W wireless',
            'Connectivity' => '5G',
            'Security' => 'Ultrasonic Fingerprint',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Phantom Gray, Phantom White, Phantom Violet, Phantom Pink'
        ]
    ],
    [
        'category_id' => $cat_ids['Galaxy S21 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S21+',
        'price' => 480000,
        'stock_quantity' => 10,
        'release_date' => '2021-01-29',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB',
            'RAM' => '8GB',
            'Display' => '6.7-inch Dynamic AMOLED 2X, 120Hz',
            'Processor' => 'Exynos 2100 / Snapdragon 888',
            'Rear Camera' => '12MP Wide, 64MP Telephoto, 12MP Ultrawide',
            'Front Camera' => '10MP',
            'Battery' => '4800 mAh, 25W wired, 15W wireless',
            'Connectivity' => '5G',
            'Security' => 'Ultrasonic Fingerprint',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Phantom Black, Phantom Silver, Phantom Violet'
        ]
    ],
    [
        'category_id' => $cat_ids['Galaxy S21 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S21 Ultra',
        'price' => 600000,
        'stock_quantity' => 8,
        'release_date' => '2021-01-29',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB',
            'RAM' => '12GB, 16GB',
            'Display' => '6.8-inch Dynamic AMOLED 2X, 120Hz',
            'Processor' => 'Exynos 2100 / Snapdragon 888',
            'Rear Camera' => '108MP Wide, 10MP Periscope, 10MP Telephoto, 12MP Ultrawide',
            'Front Camera' => '40MP',
            'Battery' => '5000 mAh, 25W wired, 15W wireless',
            'Connectivity' => '5G',
            'Security' => 'Ultrasonic Fingerprint, S Pen support',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Phantom Black, Phantom Silver, Phantom Titanium'
        ]
    ],
    [
        'category_id' => $cat_ids['Galaxy S21 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S21 FE',
        'price' => 350000,
        'stock_quantity' => 15,
        'release_date' => '2022-01-07',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB',
            'RAM' => '6GB, 8GB',
            'Display' => '6.4-inch Dynamic AMOLED 2X, 120Hz',
            'Processor' => 'Exynos 2100 / Snapdragon 888',
            'Rear Camera' => '12MP Wide, 8MP Telephoto, 12MP Ultrawide',
            'Front Camera' => '32MP',
            'Battery' => '4500 mAh, 25W wired, 15W wireless',
            'Connectivity' => '5G',
            'Security' => 'Optical Fingerprint',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'White, Graphite, Lavender, Olive'
        ]
    ],

    // S22 Series
    [
        'category_id' => $cat_ids['Galaxy S22 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S22',
        'price' => 520000,
        'stock_quantity' => 15,
        'release_date' => '2022-02-25',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB',
            'RAM' => '8GB',
            'Display' => '6.1-inch Dynamic AMOLED 2X, 120Hz',
            'Processor' => 'Exynos 2200 / Snapdragon 8 Gen 1',
            'Rear Camera' => '50MP Wide, 10MP Telephoto, 12MP Ultrawide',
            'Front Camera' => '10MP',
            'Battery' => '3700 mAh, 25W wired, 15W wireless',
            'Connectivity' => '5G',
            'Security' => 'Ultrasonic Fingerprint',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Phantom Black, White, Pink Gold, Green'
        ]
    ],
    [
        'category_id' => $cat_ids['Galaxy S22 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S22+',
        'price' => 620000,
        'stock_quantity' => 12,
        'release_date' => '2022-02-25',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB',
            'RAM' => '8GB',
            'Display' => '6.6-inch Dynamic AMOLED 2X, 120Hz',
            'Processor' => 'Exynos 2200 / Snapdragon 8 Gen 1',
            'Rear Camera' => '50MP Wide, 10MP Telephoto, 12MP Ultrawide',
            'Front Camera' => '10MP',
            'Battery' => '4500 mAh, 45W wired, 15W wireless',
            'Connectivity' => '5G',
            'Security' => 'Ultrasonic Fingerprint',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Phantom Black, White, Pink Gold, Green'
        ]
    ],
    [
        'category_id' => $cat_ids['Galaxy S22 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S22 Ultra',
        'price' => 750000,
        'stock_quantity' => 10,
        'release_date' => '2022-02-25',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB, 1TB',
            'RAM' => '8GB, 12GB',
            'Display' => '6.8-inch Dynamic AMOLED 2X, 120Hz',
            'Processor' => 'Exynos 2200 / Snapdragon 8 Gen 1',
            'Rear Camera' => '108MP Wide, 10MP Periscope, 10MP Telephoto, 12MP Ultrawide',
            'Front Camera' => '40MP',
            'Battery' => '5000 mAh, 45W wired, 15W wireless',
            'Connectivity' => '5G',
            'Security' => 'Ultrasonic Fingerprint, Built-in S Pen',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Phantom Black, White, Burgundy, Green'
        ]
    ],

    // S23 Series
    [
        'category_id' => $cat_ids['Galaxy S23 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S23',
        'price' => 680000,
        'stock_quantity' => 20,
        'release_date' => '2023-02-17',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB',
            'RAM' => '8GB',
            'Display' => '6.1-inch Dynamic AMOLED 2X, 120Hz',
            'Processor' => 'Snapdragon 8 Gen 2 for Galaxy',
            'Rear Camera' => '50MP Wide, 10MP Telephoto, 12MP Ultrawide',
            'Front Camera' => '12MP',
            'Battery' => '3900 mAh, 25W wired, 15W wireless',
            'Connectivity' => '5G',
            'Security' => 'Ultrasonic Fingerprint',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Phantom Black, Cream, Green, Lavender'
        ]
    ],
    [
        'category_id' => $cat_ids['Galaxy S23 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S23+',
        'price' => 780000,
        'stock_quantity' => 15,
        'release_date' => '2023-02-17',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '256GB, 512GB',
            'RAM' => '8GB',
            'Display' => '6.6-inch Dynamic AMOLED 2X, 120Hz',
            'Processor' => 'Snapdragon 8 Gen 2 for Galaxy',
            'Rear Camera' => '50MP Wide, 10MP Telephoto, 12MP Ultrawide',
            'Front Camera' => '12MP',
            'Battery' => '4700 mAh, 45W wired, 15W wireless',
            'Connectivity' => '5G',
            'Security' => 'Ultrasonic Fingerprint',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Phantom Black, Cream, Green, Lavender'
        ]
    ],
    [
        'category_id' => $cat_ids['Galaxy S23 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S23 Ultra',
        'price' => 950000,
        'stock_quantity' => 12,
        'release_date' => '2023-02-17',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '256GB, 512GB, 1TB',
            'RAM' => '8GB, 12GB',
            'Display' => '6.8-inch Dynamic AMOLED 2X, 120Hz',
            'Processor' => 'Snapdragon 8 Gen 2 for Galaxy',
            'Rear Camera' => '200MP Wide, 10MP Periscope, 10MP Telephoto, 12MP Ultrawide',
            'Front Camera' => '12MP',
            'Battery' => '5000 mAh, 45W wired, 15W wireless',
            'Connectivity' => '5G',
            'Security' => 'Ultrasonic Fingerprint, Built-in S Pen',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Phantom Black, Cream, Green, Lavender'
        ]
    ],
    [
        'category_id' => $cat_ids['Galaxy S23 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S23 FE',
        'price' => 550000,
        'stock_quantity' => 20,
        'release_date' => '2023-10-26',
        'is_latest' => 0,
        'specs' => [
            'Storage Options' => '128GB, 256GB',
            'RAM' => '8GB',
            'Display' => '6.4-inch Dynamic AMOLED 2X, 120Hz',
            'Processor' => 'Exynos 2200 / Snapdragon 8 Gen 1',
            'Rear Camera' => '50MP Wide, 8MP Telephoto, 12MP Ultrawide',
            'Front Camera' => '10MP',
            'Battery' => '4500 mAh, 25W wired, 15W wireless',
            'Connectivity' => '5G',
            'Security' => 'Optical Fingerprint',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Mint, Cream, Graphite, Purple'
        ]
    ],

    // S24 Series
    [
        'category_id' => $cat_ids['Galaxy S24 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S24',
        'price' => 1100000,
        'stock_quantity' => 30,
        'release_date' => '2024-01-31',
        'is_latest' => 1,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB',
            'RAM' => '8GB',
            'Display' => '6.2-inch Dynamic LTPO AMOLED 2X, 120Hz',
            'Processor' => 'Snapdragon 8 Gen 3 / Exynos 2400',
            'Rear Camera' => '50MP Wide, 10MP Telephoto, 12MP Ultrawide',
            'Front Camera' => '12MP',
            'Battery' => '4000 mAh, 25W wired, 15W wireless',
            'Connectivity' => '5G, Galaxy AI',
            'Security' => 'Ultrasonic Fingerprint',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Onyx Black, Marble Grey, Cobalt Violet, Amber Yellow'
        ]
    ],
    [
        'category_id' => $cat_ids['Galaxy S24 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S24+',
        'price' => 1350000,
        'stock_quantity' => 25,
        'release_date' => '2024-01-31',
        'is_latest' => 1,
        'specs' => [
            'Storage Options' => '256GB, 512GB',
            'RAM' => '12GB',
            'Display' => '6.7-inch Dynamic LTPO AMOLED 2X, QHD+, 120Hz',
            'Processor' => 'Snapdragon 8 Gen 3 / Exynos 2400',
            'Rear Camera' => '50MP Wide, 10MP Telephoto, 12MP Ultrawide',
            'Front Camera' => '12MP',
            'Battery' => '4900 mAh, 45W wired, 15W wireless',
            'Connectivity' => '5G, Galaxy AI',
            'Security' => 'Ultrasonic Fingerprint',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Onyx Black, Marble Grey, Cobalt Violet, Amber Yellow'
        ]
    ],
    [
        'category_id' => $cat_ids['Galaxy S24 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S24 Ultra',
        'price' => 1800000,
        'stock_quantity' => 20,
        'release_date' => '2024-01-31',
        'is_latest' => 1,
        'specs' => [
            'Storage Options' => '256GB, 512GB, 1TB',
            'RAM' => '12GB',
            'Display' => '6.8-inch Dynamic LTPO AMOLED 2X, QHD+, 120Hz, Gorilla Armor',
            'Processor' => 'Snapdragon 8 Gen 3 for Galaxy',
            'Rear Camera' => '200MP Wide, 50MP Periscope, 10MP Telephoto, 12MP Ultrawide',
            'Front Camera' => '12MP',
            'Battery' => '5000 mAh, 45W wired, 15W wireless',
            'Connectivity' => '5G, Wi-Fi 7, Galaxy AI',
            'Security' => 'Ultrasonic Fingerprint, Built-in S Pen',
            'Resistance' => 'IP68 water/dust resistance',
            'Colors' => 'Titanium Black, Titanium Gray, Titanium Violet, Titanium Yellow'
        ]
    ],
    [
        'category_id' => $cat_ids['Galaxy S24 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S24 FE',
        'price' => 950000,
        'stock_quantity' => 20,
        'release_date' => '2024-10-03',
        'is_latest' => 1,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB',
            'RAM' => '8GB',
            'Display' => '6.7-inch Dynamic AMOLED 2X, 120Hz',
            'Processor' => 'Exynos 2400e',
            'Rear Camera' => '50MP Wide, 8MP Telephoto, 12MP Ultrawide',
            'Front Camera' => '10MP',
            'Battery' => '4700 mAh, 25W wired, 15W wireless',
            'Connectivity' => '5G, Galaxy AI',
            'Security' => 'Optical Fingerprint',
            'Resistance' => 'IP68',
            'Colors' => 'Blue, Graphite, Gray, Mint, Yellow'
        ]
    ],

    // S25 Series (Upcoming/Rumored)
    [
        'category_id' => $cat_ids['Galaxy S25 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S25',
        'price' => 1250000,
        'stock_quantity' => 15,
        'release_date' => '2025-01-20',
        'is_latest' => 1,
        'specs' => [
            'Storage Options' => '128GB, 256GB, 512GB',
            'RAM' => '12GB',
            'Display' => '6.2-inch Dynamic AMOLED 2X, 120Hz',
            'Processor' => 'Snapdragon 8 Elite (Gen 4)',
            'Rear Camera' => '50MP Wide, 10MP Telephoto, 12MP Ultrawide',
            'Front Camera' => '12MP',
            'Battery' => '4000 mAh, 25W wired',
            'Connectivity' => '5G, Advanced Galaxy AI',
            'Security' => 'Ultrasonic Fingerprint',
            'Resistance' => 'IP68',
            'Colors' => 'Various expected'
        ]
    ],
    [
        'category_id' => $cat_ids['Galaxy S25 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S25+',
        'price' => 1500000,
        'stock_quantity' => 15,
        'release_date' => '2025-01-20',
        'is_latest' => 1,
        'specs' => [
            'Storage Options' => '256GB, 512GB',
            'RAM' => '12GB',
            'Display' => '6.7-inch Dynamic AMOLED 2X, 120Hz',
            'Processor' => 'Snapdragon 8 Elite (Gen 4)',
            'Rear Camera' => '50MP Wide, 10MP Telephoto, 12MP Ultrawide',
            'Front Camera' => '12MP',
            'Battery' => '4900 mAh, 45W wired',
            'Connectivity' => '5G, Advanced Galaxy AI',
            'Security' => 'Ultrasonic Fingerprint',
            'Resistance' => 'IP68',
            'Colors' => 'Various expected'
        ]
    ],
    [
        'category_id' => $cat_ids['Galaxy S25 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S25 Ultra',
        'price' => 2000000,
        'stock_quantity' => 10,
        'release_date' => '2025-01-20',
        'is_latest' => 1,
        'specs' => [
            'Storage Options' => '256GB, 512GB, 1TB',
            'RAM' => '12GB, 16GB',
            'Display' => '6.8-inch Dynamic AMOLED 2X, 120Hz, Flat display',
            'Processor' => 'Snapdragon 8 Elite (Gen 4)',
            'Rear Camera' => '200MP Wide, 50MP Periscope, 50MP Ultrawide, 10MP Telephoto',
            'Front Camera' => '12MP',
            'Battery' => '5000 mAh, 45W wired',
            'Connectivity' => '5G, Wi-Fi 7, Advanced Galaxy AI',
            'Security' => 'Ultrasonic Fingerprint, S Pen',
            'Resistance' => 'IP68',
            'Colors' => 'Titanium variants expected'
        ]
    ],
    [
        'category_id' => $cat_ids['Galaxy S25 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S25 Edge',
        'price' => 1700000,
        'stock_quantity' => 5,
        'release_date' => '2025-03-01',
        'is_latest' => 1,
        'specs' => [
            'Storage Options' => '256GB, 512GB',
            'RAM' => '12GB',
            'Display' => '6.7-inch Dynamic AMOLED 2X, 120Hz, Curved Edges',
            'Processor' => 'Snapdragon 8 Elite (Gen 4)',
            'Rear Camera' => '108MP Wide, 50MP Ultrawide, 10MP Telephoto',
            'Front Camera' => '12MP',
            'Battery' => '4900 mAh, 45W wired',
            'Connectivity' => '5G, Advanced Galaxy AI',
            'Security' => 'Ultrasonic Fingerprint',
            'Resistance' => 'IP68',
            'Colors' => 'Various expected'
        ]
    ],
    
    // S26 Series (Future rumored)
    [
        'category_id' => $cat_ids['Galaxy S26 Series'],
        'brand' => 'Samsung',
        'model' => 'Galaxy S26 Ultra',
        'price' => 2200000,
        'stock_quantity' => 5,
        'release_date' => '2026-01-15',
        'is_latest' => 1,
        'specs' => [
            'Storage Options' => '256GB, 512GB, 1TB, 2TB',
            'RAM' => '16GB',
            'Display' => '6.9-inch MicroLED or Next-gen AMOLED, 144Hz',
            'Processor' => 'Snapdragon 8 Gen 5',
            'Rear Camera' => '200MP+ Next-gen sensor stack',
            'Front Camera' => 'Under-display Camera',
            'Battery' => '5500 mAh, Solid State Battery technology',
            'Connectivity' => '6G-ready, Wi-Fi 8',
            'Security' => 'Full-screen Fingerprint, S Pen',
            'Resistance' => 'IP68/IP69',
            'Colors' => 'Next-gen Titanium'
        ]
    ]
];


$stmt = $pdo->prepare("INSERT INTO phones (category_id, brand, model, description, price, stock_quantity, release_date, is_latest, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())");
$v_stmt = $pdo->prepare("INSERT INTO phone_variants (phone_id, storage, color, price, stock_quantity) VALUES (?, ?, ?, ?, ?)");

$count = 0;
foreach ($samsung_phones as $phone) {
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

echo "Samsung Seeder finished! Inserted $count phones.\n";
