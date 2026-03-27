<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\CurrencyRateHistory;
use App\Repository\CurrencyRateHistoryRepository;

class CurrencyRateService
{
    public function __construct(
        private readonly CurrencyRateHistoryRepository $currencyRateRepository,
    ) {
    }

    public function getRateForDate(string $currency, \DateTimeInterface $date): ?CurrencyRateHistory
    {
        return $this->currencyRateRepository->findByCurrencyAndDate($currency, $date);
    }

    public function getLatestRate(string $currency): ?CurrencyRateHistory
    {
        return $this->currencyRateRepository->findLatestByCurrency($currency);
    }

    public function convertPrice(string $price, string $fromCurrency, string $toCurrency, \DateTimeInterface $date): string
    {
        if ($fromCurrency === $toCurrency) {
            return $price;
        }

        $fromRate = $this->getRateForDate($fromCurrency, $date);
        $toRate = $this->getRateForDate($toCurrency, $date);

        if (!$fromRate || !$toRate) {
            throw new \RuntimeException(sprintf('Exchange rate not found for %s or %s on date %s', $fromCurrency, $toCurrency, $date->format('Y-m-d')));
        }

        $priceInPln = bcmul($price, $fromRate->getRate(), 4);

        return bcdiv($priceInPln, $toRate->getRate(), 2);
    }

    public function fetchAndStoreRateFromNbp(string $currency, \DateTimeInterface $date): CurrencyRateHistory
    {
        $rate = $this->fetchFromNbpApi($currency, $date);

        $currencyRateHistory = CurrencyRateHistory::create($currency, $rate, $date, 'NBP');
        $this->currencyRateRepository->save($currencyRateHistory, true);

        return $currencyRateHistory;
    }

    private function fetchFromNbpApi(string $currency, \DateTimeInterface $date): string
    {
        // TODO: Implement actual NBP API integration
        // Endpoint: http://api.nbp.pl/api/exchangerates/rates/A/{currency}/{date}/
        // Response format: JSON with rates array containing mid value

        throw new \RuntimeException('NBP API integration not yet implemented. This is a placeholder for future implementation.');
    }

    public function fetchLatestRatesFromNbp(): array
    {
        $currencies = ['EUR', 'USD'];
        $results = [];

        foreach ($currencies as $currency) {
            try {
                $results[$currency] = $this->fetchAndStoreRateFromNbp($currency, new \DateTime());
            } catch (\RuntimeException $e) {
                $results[$currency] = null;
            }
        }

        return $results;
    }
}
