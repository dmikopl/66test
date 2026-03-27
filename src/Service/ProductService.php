<?php

namespace App\Service;

use App\Entity\PriceHistory;
use App\Entity\Product;
use App\Event\ProductPriceChanged;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

class ProductService
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function create(string $name, string $sku, string $price, string $currency): Product
    {
        $this->validatePrice($price);
        $this->validateCurrency($currency);
        $this->validateSku($sku);

        $product = new Product();
        $product->setName($name);
        $product->setSku($sku);
        $product->setPrice($price);
        $product->setCurrency($currency);

        $this->productRepository->save($product, true);

        return $product;
    }

    public function changePrice(Product $product, string $newPrice, string $currency, ?int $expectedVersion = null): void
    {
        if ($expectedVersion !== null && $product->getVersion() !== $expectedVersion) {
            throw new \RuntimeException('Product has been modified by another user');
        }

        $this->validatePrice($newPrice);
        $this->validateCurrency($currency);

        $oldPrice = $product->getPrice();

        if ($oldPrice === $newPrice) {
            return;
        }

        $priceHistory = PriceHistory::create($product, $oldPrice, $newPrice, $currency);
        $product->addPriceHistory($priceHistory);
        $product->setPrice($newPrice);
        $product->setCurrency($currency);

        $this->productRepository->save($product, true);

        $event = new ProductPriceChanged(
            $product->getId(),
            $oldPrice,
            $newPrice,
            $currency,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($event);
    }

    public function update(Product $product, ?string $name = null, ?string $sku = null, ?int $expectedVersion = null): void
    {
        if ($expectedVersion !== null && $product->getVersion() !== $expectedVersion) {
            throw new \RuntimeException('Product has been modified by another user');
        }

        if ($name !== null) {
            $product->setName($name);
        }

        if ($sku !== null) {
            $this->validateSku($sku, $product->getId());
            $product->setSku($sku);
        }

        $this->productRepository->save($product, true);
    }

    public function softDelete(Product $product): void
    {
        $product->softDelete();
        $this->productRepository->save($product, true);
    }

    public function restore(Product $product): void
    {
        $product->restore();
        $this->productRepository->save($product, true);
    }

    private function validatePrice(string $price): void
    {
        if (bccomp($price, '0', 2) <= 0) {
            throw new \InvalidArgumentException('Price must be greater than 0');
        }
    }

    private function validateCurrency(string $currency): void
    {
        if (!in_array($currency, Product::ALLOWED_CURRENCIES, true)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid currency. Allowed: %s', implode(', ', Product::ALLOWED_CURRENCIES))
            );
        }
    }

    private function validateSku(string $sku, ?string $excludeProductId = null): void
    {
        if (empty(trim($sku))) {
            throw new \InvalidArgumentException('SKU cannot be empty');
        }

        if ($this->productRepository->isSkuTaken($sku, $excludeProductId)) {
            throw new \InvalidArgumentException('SKU already exists for active product');
        }
    }
}
