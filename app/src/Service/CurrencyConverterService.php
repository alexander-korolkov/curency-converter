<?php

namespace App\Service;

use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use RuntimeException;

class CurrencyConverterService
{
    private const ACCURACY = 18;

    private const CONVERT_THROUGH = ['USD', 'EUR', 'RUB'];

    private const CRYPTO_CURRENCIES = [
        'BCH' => 'Bitcoin Cash',
        'XRP' => 'Ripple',
        'BTC' => 'Bitcoin',
        'BTG' => 'Bitcoin Gold',
        'ETH' => 'Ethereum',
        'ZEC' => 'Zcash',
    ];

    private CurrencyRateFetcherService $currencyRateFetcherService;

    private CurrenciesListFetcherService $currenciesListFetcherService;

    private CacheInterface $cache;

    private LoggerInterface $logger;

    public function __construct(CurrencyRateFetcherService $currencyRateFetcherService, CurrenciesListFetcherService $currenciesListFetcherService, CacheInterface $cache, LoggerInterface $logger)
    {
        $this->currencyRateFetcherService = $currencyRateFetcherService;
        $this->currenciesListFetcherService = $currenciesListFetcherService;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function convert($from, $to, $amount): string
    {
        $rate = $this->getRate($from, $to);
        $convertedAmount = $this->multiplication((string)$amount, $rate);

        return $this->formatCurrency($convertedAmount, $to);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getRate($from, $to): string
    {
        static $rate = null;
        if ($rate) {

            return $rate;
        }

        if ($this->isPairAvailable("$from$to")) {
            $rate = $this->currencyRateFetcherService->fetchRate($from, $to);

            return $this->formatRate($rate);
        }

        foreach (self::CONVERT_THROUGH as $mediateCurrency) {
            if ($this->isPairAvailable("$from$mediateCurrency") && $this->isPairAvailable("$mediateCurrency$to")) {
                $rateFromMediateCurrency = $this->currencyRateFetcherService->fetchRate($from, $mediateCurrency);
                $rateToMediateCurrency = $this->currencyRateFetcherService->fetchRate($mediateCurrency, $to);
                $rate = $this->multiplication($rateFromMediateCurrency, $rateToMediateCurrency);

                return $this->formatRate($rate);
            }
            // Remember the mediate currencies for both ($front and $to)
            // in case if it's not possible to convert only through one mediate currency.
            if ($this->isPairAvailable("$from$mediateCurrency")) {
                $mediateCurrencyFrom = $mediateCurrency;
            }
            if ($this->isPairAvailable("$mediateCurrency$to")) {
                $mediateCurrencyTo = $mediateCurrency;
            }
        }

        if (isset($mediateCurrencyFrom) && isset($mediateCurrencyTo)) {
            $rateFrom = $this->currencyRateFetcherService->fetchRate($from, $mediateCurrencyFrom);
            $rateMediate = $this->currencyRateFetcherService->fetchRate($mediateCurrencyFrom, $mediateCurrencyTo);
            $rateTo = $this->currencyRateFetcherService->fetchRate($mediateCurrencyTo, $to);
            $rate = $this->multiplication($rateFrom, $rateMediate);
            $rate = $this->multiplication($rate, $rateTo);
        } else {
            $this->logger->error('Error in CurrencyConverterService during currency pair calculation');
            throw new RuntimeException('Can\'t calculate rate for this currency pair.');
        }

        return $this->formatRate($rate);
    }

    private function formatRate(string $num): string
    {
        // Just remove unnecessary zeros
        if (str_contains($num, '.')) {
            $num = rtrim($num, '0');
            $num = rtrim($num, '.');
        }

        return $num;
    }

    private function formatCurrency(string $number, string $currency): string
    {
        $number = $this->formatRate($number);

        if ($this->isCrypto($currency)) {
            return $number;
        }

        return number_format((float)$number, 2, '.', '');
    }

    private function isCrypto(string $currencyCode): bool
    {

        return isset(self::CRYPTO_CURRENCIES[$currencyCode]);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function isPairAvailable($pair): bool
    {
        // Added cache here in order to increase performance.
        return $this->cache->get("{$pair}_is_available", function () use ($pair): bool {
            $list = $this->currenciesListFetcherService->fetchCurrencyPairs();
            $pairs = array_fill_keys($list, true);
            $invertedPair =  substr($pair, 3, 3).substr($pair, 0, 3);

            return isset($pairs[$pair]) || isset($pairs[$invertedPair]);
        });
    }

    private function multiplication(string $multiplier, string $multiplicand):string
    {

        return bcmul($multiplier, $multiplicand, self::ACCURACY);
    }
}
