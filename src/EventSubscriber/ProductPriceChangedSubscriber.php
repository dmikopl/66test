<?php

namespace App\EventSubscriber;

use App\Event\ProductPriceChanged;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductPriceChangedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductPriceChanged::class => 'onProductPriceChanged',
        ];
    }

    public function onProductPriceChanged(ProductPriceChanged $event): void
    {
        $this->logger->info('Product price changed', [
            'product_id' => $event->getProductId(),
            'old_price' => $event->getOldPrice(),
            'new_price' => $event->getNewPrice(),
            'currency' => $event->getCurrency(),
            'occurred_at' => $event->getOccurredAt()->format('Y-m-d H:i:s'),
        ]);
    }
}
