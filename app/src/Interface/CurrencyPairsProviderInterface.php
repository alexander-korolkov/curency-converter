<?php

namespace App\Interface;

interface CurrencyPairsProviderInterface
{
    public function fetchCurrencyPairs(): array;
}
