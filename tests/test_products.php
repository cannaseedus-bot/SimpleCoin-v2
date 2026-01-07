<?php
echo "🔎 Testing products.json...\n";

$json = file_get_contents(__DIR__ . '/../products.json');
$products = json_decode($json, true);

if (!is_array($products)) {
    exit("❌ products.json must be an array.\n");
}

foreach ($products as $i => $p) {
    if (empty($p['id']) || empty($p['name']) || empty($p['price']) || empty($p['photo'])) {
        exit("❌ Product #$i missing required fields.\n");
    }

    if (!is_numeric($p['price'])) {
        exit("❌ Product {$p['name']} has invalid price type.\n");
    }
}

echo "✅ products.json format looks good.\n";
