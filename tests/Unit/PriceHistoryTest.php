<?php

namespace App\Tests\Unit;

use App\Entity\PriceHistory;
use PHPUnit\Framework\TestCase;

class PriceHistoryTest extends TestCase
{
    public function testPriceHistoryCreation(): void
    {
        $product = new \App\Entity\Product();
        $history = PriceHistory::create($product, '10.00', '15.00', 'PLN');

        $this->assertNotNull($history->getId());
        $this->assertSame($product, $history->getProduct());
        $this->assertEquals('10.00', $history->getOldPrice());
        $this->assertEquals('15.00', $history->getNewPrice());
        $this->assertEquals('PLN', $history->getCurrency());
        $this->assertNotNull($history->getChangedAt());
    }

    public function testPriceHistorySetters(): void
    {
        $history = new PriceHistory();
        $product = new \App\Entity\Product();

        $history->setProduct($product);
        $history->setOldPrice('20.00');
        $history->setNewPrice('25.00');
        $history->setCurrency('EUR');

        $this->assertSame($product, $history->getProduct());
        $this->assertEquals('20.00', $history->getOldPrice());
        $this->assertEquals('25.00', $history->getNewPrice());
        $this->assertEquals('EUR', $history->getCurrency());
    }
}
