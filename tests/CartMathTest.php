<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class CartMathTest extends TestCase
{
    public function testCartTotalsMatchExpected(): void
    {
        $cart = [
            [ 'name' => 'Product A', 'price' => 10.0, 'quantity' => 2 ],
            [ 'name' => 'Product B', 'price' => 7.5, 'quantity' => 1 ],
        ];

        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $this->assertSame('27.50', number_format($total, 2), 'Cart total miscalculated.');
    }
}
