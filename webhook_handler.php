<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
date_default_timezone_set('UTC');

$secret = getenv('COINBASE_WEBHOOK_SECRET');
$payload = file_get_contents('php://input');
$signatureHeader = $_SERVER['HTTP_X_CC_WEBHOOK_SIGNATURE'] ?? '';

$logDir = __DIR__ . '/logs';
$failureLog = $logDir . '/webhook_failures.log';
$orderLog = $logDir . '/order_updates.log';

if (!is_dir($logDir) && !mkdir($logDir, 0750, true) && !is_dir($logDir)) {
    http_response_code(500);
    exit('Unable to prepare log directory.');
}

if (!$secret) {
    logFailure('Webhook secret is not configured.');
    http_response_code(500);
    exit('Webhook secret not configured.');
}

$computedSig = hash_hmac('sha256', $payload, $secret);
if (!hash_equals($computedSig, $signatureHeader)) {
    logFailure('Signature mismatch.');
    http_response_code(400);
    exit('Invalid signature');
}

$data = json_decode($payload, true);
if (!is_array($data)) {
    logFailure('Invalid JSON payload.');
    http_response_code(400);
    exit('Bad request');
}

$event = $data['event']['type'] ?? '';
$metadata = $data['event']['data']['metadata'] ?? [];
$orderId = $metadata['order_id'] ?? null;
$email = $metadata['email'] ?? 'unknown';
$amount = $data['event']['data']['pricing']['local']['amount'] ?? '0.00';

switch ($event) {
    case 'charge:confirmed':
        updateOrderStatus($orderId, 'paid', $email);
        echo "✅ Payment confirmed for {$email} ({$amount})";
        break;
    case 'charge:pending':
        updateOrderStatus($orderId, 'pending', $email);
        echo '⏳ Payment pending';
        break;
    case 'charge:failed':
    case 'charge:expired':
        updateOrderStatus($orderId, 'failed', $email);
        echo '❌ Payment failed or expired';
        break;
    default:
        logFailure("Unhandled event type: {$event}");
        echo 'Event logged.';
}

http_response_code(200);
exit;

function updateOrderStatus(?string $orderId, string $status, string $email = 'unknown'): void
{
    global $orderLog;

    if (!$orderId) {
        logFailure("Missing order ID for status update to {$status}");
        return;
    }

    $pdo = getPdo();

    $update = $pdo->prepare(
        'UPDATE orders SET payment_status = :status, updated_at = CURRENT_TIMESTAMP WHERE order_id = :order_id'
    );
    $update->execute(['status' => $status, 'order_id' => $orderId]);

    if ($update->rowCount() === 0) {
        $insert = $pdo->prepare(
            'INSERT INTO orders (order_id, user_email, payment_status, created_at, updated_at)
             VALUES (:order_id, :email, :status, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)'
        );
        $insert->execute(['order_id' => $orderId, 'email' => $email, 'status' => $status]);
    }

    error_log("Order {$orderId} marked {$status}\n", 3, $orderLog);
}

function logFailure(string $message): void
{
    global $failureLog;
    error_log($message . "\n", 3, $failureLog);
}
