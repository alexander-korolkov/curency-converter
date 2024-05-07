<?php

namespace App\Service;

use App\Interface\CurrenciesListProviderInterface;
use Psr\Cache\InvalidArgumentException;

class CurrenciesListFetcherService
{
    private CurrenciesListProviderInterface $listProvider;

    public function __construct(CurrenciesListProviderInterface $listProvider)
    {
        $this->listProvider = $listProvider;
    }

    public function fetchCurrenciesList(): array
    {

        return $this->listProvider->fetchCurrenciesList();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function fetchCurrencyPairs(): array
    {

        return $this->listProvider->fetchCurrencyPairs();
    }
}
