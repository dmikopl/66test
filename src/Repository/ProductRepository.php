<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

    public function findActivePaginated(int $page, int $limit, ?string $status = null): Paginator
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.deletedAt IS NULL');

        if ($status) {
            $qb->andWhere('p.status = :status')
               ->setParameter('status', $status);
        }

        $qb->orderBy('p.createdAt', 'DESC')
           ->setFirstResult(($page - 1) * $limit)
           ->setMaxResults($limit);

        return new Paginator($qb);
    }

    public function countActive(?string $status = null): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.deletedAt IS NULL');

        if ($status) {
            $qb->andWhere('p.status = :status')
               ->setParameter('status', $status);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    public function isSkuTaken(string $sku, ?string $excludeProductId = null): bool
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.sku = :sku')
            ->andWhere('p.status = :status')
            ->andWhere('p.deletedAt IS NULL')
            ->setParameter('sku', $sku)
            ->setParameter('status', Product::STATUS_ACTIVE);

        if ($excludeProductId) {
            $qb->andWhere('p.id != :excludeId')
               ->setParameter('excludeId', $excludeProductId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function save(Product $product, bool $flush = false): void
    {
        $this->getEntityManager()->persist($product);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
