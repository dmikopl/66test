<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function testProductCreation(): void
    {
        $product = new \App\Entity\Product();

        $this->assertNotNull($product->getId());
        $this->assertEquals(\App\Entity\Product::STATUS_ACTIVE, $product->getStatus());
        $this->assertNotNull($product->getCreatedAt());
        $this->assertNotNull($product->getUpdatedAt());
        $this->assertNull($product->getDeletedAt());
    }

    public function testProductStatus(): void
    {
        $product = new \App\Entity\Product();

        $this->assertTrue($product->isActive());

        $product->setStatus(\App\Entity\Product::STATUS_INACTIVE);
        $this->assertFalse($product->isActive());
    }

    public function testSoftDelete(): void
    {
        $product = new \App\Entity\Product();

        $product->softDelete();

        $this->assertNotNull($product->getDeletedAt());
        $this->assertEquals(\App\Entity\Product::STATUS_INACTIVE, $product->getStatus());
    }

    public function testRestore(): void
    {
        $product = new \App\Entity\Product();

        $product->softDelete();
        $product->restore();

        $this->assertNull($product->getDeletedAt());
        $this->assertEquals(\App\Entity\Product::STATUS_ACTIVE, $product->getStatus());
    }

    public function testPriceHistories(): void
    {
        $product = new \App\Entity\Product();

        $this->assertEmpty($product->getPriceHistories());

        $history = \App\Entity\PriceHistory::create($product, '10.00', '15.00', 'PLN');
        $product->addPriceHistory($history);

        $this->assertCount(1, $product->getPriceHistories());
        $this->assertSame($history, $product->getPriceHistories()->first());
    }
}
