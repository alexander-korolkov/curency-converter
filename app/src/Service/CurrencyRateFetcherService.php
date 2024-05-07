<?php

namespace App\Service;

use App\Interface\CurrencyRateProviderInterface;

class CurrencyRateFetcherService
{
    private CurrencyRateProviderInterface $rateProvider;

    public function __construct(CurrencyRateProviderInterface $rateProvider)
    {
        $this->rateProvider = $rateProvider;
    }

    public function fetchRate(string $fromCurrency, string $toCurrency): ?string
    {

        return $this->rateProvider->fetchRate($fromCurrency, $toCurrency);
    }

    public function fetchAllRates(): array
    {

        return $this->rateProvider->fetchAllRates();
    }
}
