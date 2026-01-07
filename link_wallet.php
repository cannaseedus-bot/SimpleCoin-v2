<?php
session_start();
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

$userEmail = $_SESSION['user_email'] ?? null;
if (!$userEmail) {
    http_response_code(401);
    echo 'Not logged in';
    exit;
}

$type = $_POST['type'] ?? '';
$address = trim((string)($_POST['address'] ?? ''));
$allowed = ['metamask' => 'metamask_address', 'coinbase' => 'coinbase_address'];

if (!isset($allowed[$type])) {
    http_response_code(400);
    echo 'Invalid wallet type';
    exit;
}

if (!preg_match('/^0x[a-fA-F0-9]{40}$/', $address)) {
    http_response_code(400);
    echo 'Invalid wallet address format';
    exit;
}

$pdo = getPdo();
$stmt = $pdo->prepare("UPDATE users SET {$allowed[$type]} = :address WHERE email = :email");
$stmt->execute(['address' => $address, 'email' => $userEmail]);

echo "✅ {$type} wallet linked: {$address}";
