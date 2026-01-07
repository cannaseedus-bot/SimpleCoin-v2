<?php
session_start();
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$wallet = trim((string)($_POST['wallet_address'] ?? ''));
$tier = trim((string)($_POST['tier'] ?? ''));
$allowedTiers = ['bronze', 'silver', 'gold'];

if ($wallet === '' || !in_array($tier, $allowedTiers, true)) {
    http_response_code(400);
    echo 'Invalid request';
    exit;
}

$userEmail = $_SESSION['user_email'] ?? null;
$pdo = getPdo();
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
$stmt->execute(['email' => $userEmail]);
$user = $stmt->fetch();

if (!$user || (int)$user['is_admin'] !== 1) {
    http_response_code(403);
    echo 'Unauthorized';
    exit;
}

$metadataPath = __DIR__ . "/nft_rewards/{$tier}.json";
if (!is_file($metadataPath)) {
    http_response_code(500);
    echo 'Metadata missing for selected tier.';
    exit;
}

$metadata = json_decode((string)file_get_contents($metadataPath), true);
if (!is_array($metadata)) {
    http_response_code(500);
    echo 'Unable to parse metadata.';
    exit;
}

$record = [
    'wallet' => $wallet,
    'tier' => $tier,
    'metadata' => $metadata,
    'timestamp' => date('c'),
    'initiated_by' => $userEmail,
];

$logsDir = __DIR__ . '/mint_logs';
if (!is_dir($logsDir) && !mkdir($logsDir, 0750, true) && !is_dir($logsDir)) {
    http_response_code(500);
    echo 'Unable to create logs directory.';
    exit;
}

$outputPath = $logsDir . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $wallet) . "_{$tier}.json";
file_put_contents($outputPath, json_encode($record, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "✅ NFT minted to {$wallet} ({$tier} tier). Metadata logged to mint_logs.";
