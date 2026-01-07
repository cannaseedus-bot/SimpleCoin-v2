<?php
declare(strict_types=1);

define('CART_COOKIE_NAME', 'asx_cart');
$cartSecret = getenv('CART_SECRET_KEY') ?: 'change_this_secret_key_to_32_characters';
define('CART_SECRET_KEY', $cartSecret);

/**
 * Persist the cart as a signed cookie to prevent tampering.
 */
function setSignedCartCookie(array $cart): void
{
    $payload = json_encode($cart);
    if ($payload === false) {
        error_log('Unable to encode cart payload for cookie storage.');
        return;
    }

    $signature = hash_hmac('sha256', $payload, CART_SECRET_KEY);
    $cookieValue = base64_encode($payload) . '.' . $signature;

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] === '443';
    setcookie(
        CART_COOKIE_NAME,
        $cookieValue,
        [
            'expires' => time() + (86400 * 30),
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );
}

/**
 * Retrieve and verify a signed cart cookie.
 */
function getCartFromCookie(): array
{
    if (!isset($_COOKIE[CART_COOKIE_NAME])) {
        return [];
    }

    [$payloadB64, $signature] = explode('.', $_COOKIE[CART_COOKIE_NAME] ?? '', 2) + ['', ''];
    $payload = base64_decode($payloadB64, true);
    if ($payload === false) {
        return [];
    }

    $expectedSig = hash_hmac('sha256', $payload, CART_SECRET_KEY);

    if (!hash_equals($expectedSig, $signature)) {
        error_log('⚠️ Cart cookie tampering detected.');
        return [];
    }

    $decoded = json_decode($payload, true);
    return is_array($decoded) ? $decoded : [];
}
