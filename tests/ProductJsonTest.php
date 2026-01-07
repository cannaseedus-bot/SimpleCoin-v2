<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ProductJsonTest extends TestCase
{
    /** @var array<int, array<string, mixed>> */
    private array $products = [];

    protected function setUp(): void
    {
        $json = file_get_contents(__DIR__ . '/../products.json');
        $this->assertNotFalse($json, 'products.json should be readable.');

        $products = json_decode($json, true);
        $this->assertIsArray($products, 'products.json must be an array.');

        $this->products = $products;
    }

    public function testProductsContainRequiredFields(): void
    {
        foreach ($this->products as $i => $product) {
            $this->assertNotEmpty($product['id'] ?? null, "Product #{$i} missing id.");
            $this->assertNotEmpty($product['name'] ?? null, "Product #{$i} missing name.");
            $this->assertNotEmpty($product['price'] ?? null, "Product #{$i} missing price.");
            $this->assertNotEmpty($product['photo'] ?? null, "Product #{$i} missing photo.");
        }
    }

    public function testProductPricesAreNumeric(): void
    {
        foreach ($this->products as $product) {
            $this->assertIsNumeric($product['price'], "Product {$product['name']} has invalid price type.");
        }
    }
}
