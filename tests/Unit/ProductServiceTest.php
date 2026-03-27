<?php

namespace App\Tests\Unit;

use App\Entity\Product;
use App\Service\ProductService;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

class ProductServiceTest extends TestCase
{
    private ProductService $productService;
    private ProductRepository $repository;
    private EventDispatcherInterface $eventDispatcher;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ProductRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->productService = new ProductService(
            $this->repository,
            $entityManager,
            $this->eventDispatcher
        );
    }

    public function testProductCreation(): void
    {
        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Product::class), true);

        $product = $this->productService->create('Test Product', 'SKU123', '10.00', 'PLN');

        $this->assertEquals('Test Product', $product->getName());
        $this->assertEquals('SKU123', $product->getSku());
        $this->assertEquals('10.00', $product->getPrice());
        $this->assertEquals('PLN', $product->getCurrency());
    }

    public function testPriceChange(): void
    {
        $product = new Product();
        $product->setPrice('10.00');
        $product->setCurrency('PLN');

        $this->repository->expects($this->once())
            ->method('save')
            ->with($product, true);

        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(\App\Event\ProductPriceChanged::class));

        $this->productService->changePrice($product, '15.00', 'PLN');

        $this->assertEquals('15.00', $product->getPrice());
        $this->assertCount(1, $product->getPriceHistories());
    }

    public function testPriceChangeWithSamePrice(): void
    {
        $product = new Product();
        $product->setPrice('10.00');
        $product->setCurrency('PLN');

        $this->repository->expects($this->never())
            ->method('save');

        $this->productService->changePrice($product, '10.00', 'PLN');

        $this->assertEquals('10.00', $product->getPrice());
        $this->assertEmpty($product->getPriceHistories());
    }

    public function testOptimisticLocking(): void
    {
        $product = new Product();
        $product->setPrice('10.00');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Product has been modified by another user');

        $this->productService->changePrice($product, '15.00', 'PLN', 999);
    }

    public function testInvalidPrice(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Price must be greater than 0');

        $this->productService->create('Test', 'SKU123', '-10.00', 'PLN');
    }

    public function testInvalidCurrency(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid currency');

        $this->productService->create('Test', 'SKU123', '10.00', 'INVALID');
    }
}
