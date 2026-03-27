<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CurrencyRateHistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: CurrencyRateHistoryRepository::class)]
#[ORM\Table(name: 'currency_rate_history')]
#[ORM\Index(fields: ['currency', 'rateDate'], name: 'idx_currency_date')]
class CurrencyRateHistory
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private ?string $id = null;

    #[ORM\Column(length: 3)]
    private ?string $currency = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 4)]
    private ?string $rate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $rateDate = null;

    #[ORM\Column(length: 10)]
    private ?string $source = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fetchedAt = null;

    public function __construct()
    {
        $this->id = Uuid::v4()->toRfc4122();
        $this->fetchedAt = new \DateTime();
    }

    public static function create(string $currency, string $rate, \DateTimeInterface $rateDate, string $source = 'NBP'): self
    {
        $history = new self();
        $history->setCurrency($currency);
        $history->setRate($rate);
        $history->setRateDate($rateDate);
        $history->setSource($source);

        return $history;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getRate(): ?string
    {
        return $this->rate;
    }

    public function setRate(string $rate): static
    {
        $this->rate = $rate;

        return $this;
    }

    public function getRateDate(): ?\DateTimeInterface
    {
        return $this->rateDate;
    }

    public function setRateDate(\DateTimeInterface $rateDate): static
    {
        $this->rateDate = $rateDate;

        return $this;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(string $source): static
    {
        $this->source = $source;

        return $this;
    }

    public function getFetchedAt(): ?\DateTimeInterface
    {
        return $this->fetchedAt;
    }

    public function setFetchedAt(\DateTimeInterface $fetchedAt): static
    {
        $this->fetchedAt = $fetchedAt;

        return $this;
    }
}
