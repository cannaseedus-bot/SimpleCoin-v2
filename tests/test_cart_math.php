<?php
echo "🧮 Testing cart math...\n";

$cart = [
    [ "name" => "Product A", "price" => 10.0, "quantity" => 2 ],
    [ "name" => "Product B", "price" => 7.5, "quantity" => 1 ]
];

$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

if (number_format($total, 2) !== '27.50') {
    exit("❌ Cart total miscalculated. Found: $total\n");
}

echo "✅ Cart math validated: $" . number_format($total, 2) . "\n";
