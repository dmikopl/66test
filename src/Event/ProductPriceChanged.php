<?php

declare(strict_types=1);

namespace App\Event;

class ProductPriceChanged
{
    public function __construct(
        private readonly string $productId,
        private readonly string $oldPrice,
        private readonly string $newPrice,
        private readonly string $currency,
        private readonly \DateTimeInterface $occurredAt
    ) {
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getOldPrice(): string
    {
        return $this->oldPrice;
    }

    public function getNewPrice(): string
    {
        return $this->newPrice;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getOccurredAt(): \DateTimeInterface
    {
        return $this->occurredAt;
    }
}
