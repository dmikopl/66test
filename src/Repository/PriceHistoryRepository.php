<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PriceHistory;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PriceHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PriceHistory::class);
    }

    public function findByProduct(Product $product): array
    {
        return $this->createQueryBuilder('ph')
            ->where('ph.product = :product')
            ->orderBy('ph.changedAt', 'DESC')
            ->setParameter('product', $product)
            ->getQuery()
            ->getResult();
    }

    public function save(PriceHistory $priceHistory, bool $flush = false): void
    {
        $this->getEntityManager()->persist($priceHistory);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
