<?php
declare(strict_types=1);

require_once __DIR__ . '/cart_cookie.php';

header('Content-Type: application/json');

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON payload.']);
    exit;
}

$cart = $data['cart'] ?? [];
if (!is_array($cart)) {
    $cart = [];
}

setSignedCartCookie($cart);

echo json_encode(['status' => '✅ cart saved']);
