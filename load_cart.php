<?php
declare(strict_types=1);

require_once __DIR__ . '/cart_cookie.php';

header('Content-Type: application/json');

$cart = getCartFromCookie();
echo json_encode($cart);
