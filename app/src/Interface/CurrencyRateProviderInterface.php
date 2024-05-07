<?php

namespace App\Interface;

interface CurrencyRateProviderInterface
{
    public function fetchRate(string $fromCurrency, string $toCurrency): ?string;

    public function fetchAllRates();
}
