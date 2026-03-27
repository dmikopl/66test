<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findActiveBySku(string $sku): ?Product
    {
        return $this->createQueryBuilder('p')
            ->where('p.sku = :sku')
            ->andWhere('p.status = :status')
            ->andWhere('p.deletedAt IS NULL')
            ->setParameter('sku', $sku)
            ->setParameter('status', Product::STATUS_ACTIVE)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveById(string $id): ?Product
    {
        return $this->createQueryBuilder('p')
            ->where('p.id = :id')
            ->andWhere('p.status = :status')
            ->andWhere('p.deletedAt IS NULL')
            ->setParameter('id', $id)
            ->setParameter('status', Product::STATUS_ACTIVE)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Product $product, bool $flush = false): void
    {
        $this->getEntityManager()->persist($product);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
