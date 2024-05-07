<?php

namespace App\Interface;

interface CurrenciesListProviderInterface
{
    public function fetchCurrenciesList(): array;
}
