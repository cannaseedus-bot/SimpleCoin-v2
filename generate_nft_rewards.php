<?php
$tiers = [
    'bronze' => [
        'price' => 1,
        'title' => 'Bronze Loyalty NFT',
        'description' => 'Issued to ASX buyers as a standard collectible reward.',
        'color' => '#cd7f32',
        'rarity' => 'common',
    ],
    'silver' => [
        'price' => 5,
        'title' => 'Silver Loyalty NFT',
        'description' => 'Awarded for premium purchases. Includes badge-level status.',
        'color' => '#c0c0c0',
        'rarity' => 'uncommon',
    ],
    'gold' => [
        'price' => 20,
        'title' => 'Gold Loyalty NFT',
        'description' => 'Elite-tier NFT for high-value buyers. Rare collectible.',
        'color' => '#ffd700',
        'rarity' => 'rare',
    ],
];

$dir = __DIR__ . '/nft_rewards';
if (!is_dir($dir) && !mkdir($dir, 0750, true) && !is_dir($dir)) {
    throw new RuntimeException('Unable to create nft_rewards directory.');
}

foreach ($tiers as $tier => $data) {
    $svg = <<<SVG
<svg width="300" height="300" xmlns="http://www.w3.org/2000/svg">
  <rect width="300" height="300" rx="20" ry="20" fill="{$data['color']}" />
  <text x="150" y="160" font-size="28" fill="#000" text-anchor="middle" font-family="Arial" font-weight="bold">{$tier}</text>
</svg>
SVG;

    file_put_contents("{$dir}/{$tier}.svg", $svg);

    $json = [
        'name' => $data['title'],
        'description' => $data['description'],
        'rarity' => $data['rarity'],
        'tier' => $tier,
        'price' => $data['price'],
        'image' => "{$tier}.svg",
        'attributes' => [
            ['trait_type' => 'Tier', 'value' => ucfirst($tier)],
            ['trait_type' => 'Rarity', 'value' => $data['rarity']],
            ['trait_type' => 'ASX Backed', 'value' => 'Yes'],
        ],
    ];

    file_put_contents("{$dir}/{$tier}.json", json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

echo "✅ NFT rewards written to nft_rewards/ directory." . PHP_EOL;
