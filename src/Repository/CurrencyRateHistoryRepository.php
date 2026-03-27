<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CurrencyRateHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CurrencyRateHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CurrencyRateHistory::class);
    }

    public function findByCurrencyAndDate(string $currency, \DateTimeInterface $date): ?CurrencyRateHistory
    {
        return $this->createQueryBuilder('c')
            ->where('c.currency = :currency')
            ->andWhere('c.rateDate = :date')
            ->setParameter('currency', $currency)
            ->setParameter('date', $date)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findLatestByCurrency(string $currency): ?CurrencyRateHistory
    {
        return $this->createQueryBuilder('c')
            ->where('c.currency = :currency')
            ->setParameter('currency', $currency)
            ->orderBy('c.rateDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findRatesForDateRange(string $currency, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.currency = :currency')
            ->andWhere('c.rateDate >= :startDate')
            ->andWhere('c.rateDate <= :endDate')
            ->setParameter('currency', $currency)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('c.rateDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function save(CurrencyRateHistory $currencyRateHistory, bool $flush = false): void
    {
        $this->getEntityManager()->persist($currencyRateHistory);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
