<?php
// File: create coinbase charge 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $total = $_POST['total'] ?? 0;
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: '';
    $orderId = trim((string)($_POST['order_id'] ?? ''));

    if ($orderId === '') {
        $orderId = 'order-' . bin2hex(random_bytes(6));
    }

    $url = 'https://api.commerce.coinbase.com/charges';
    $apiKey = getenv('COINBASE_API_KEY');

    if (!$apiKey) {
        http_response_code(500);
        echo json_encode(['error' => 'Coinbase API key is not configured']);
        exit;
    }

    $postData = [
        'name' => 'Cart Total Charge',
        'description' => 'Charge for items in cart',
        'pricing_type' => 'fixed_price',
        'local_price' => [
            'amount' => $total,
            'currency' => 'USD'
        ],
        'redirect_url' => 'https://bitcoin.cannabis-seed.us/payment-success.html',
        'cancel_url' => 'https://bitcoin.cannabis-seed.us/payment-cancel.html',
        'metadata' => [
            'order_id' => $orderId,
            'email' => $email,
        ],
    ];

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'X-CC-Api-Key: ' . $apiKey,
        'X-CC-Version: 2018-03-22'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $responseData = json_decode($response, true);

    $checkoutUrl = $responseData['data']['hosted_url'] ?? null;
    $chargeId = $responseData['data']['id'] ?? null;

    if ($httpCode >= 200 && $httpCode < 300 && $checkoutUrl) {
        echo json_encode([
            'checkoutUrl' => $checkoutUrl,
            'hosted_url' => $checkoutUrl,
            'charge_id' => $chargeId,
            'order_id' => $orderId,
        ]);
    } else {
        echo json_encode(['error' => 'Failed to create charge', 'details' => $responseData]);
    }
}
?>
