<?php
namespace App\Provider;

use App\Interface\CurrenciesListProviderInterface;
use App\Interface\CurrencyPairsProviderInterface;
use App\Interface\CurrencyRateProviderInterface;
use App\Service\RetrieveDataService;
use DateTimeImmutable;
use Psr\Cache\InvalidArgumentException;
use RuntimeException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CurrateProvider implements CurrencyRateProviderInterface, CurrenciesListProviderInterface, CurrencyPairsProviderInterface
{
    private const RATE_TTL = 600;

    private RetrieveDataService $retrieveDataService;

    private string $apiKey;

    private string $baseUrl;

    private CacheInterface $cache;

    public function __construct(RetrieveDataService $retrieveDataService, string $apiKey, string $baseUrl, CacheInterface $cache)
    {
        $this->retrieveDataService = $retrieveDataService;
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
        $this->cache = $cache;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function fetchRate(string $fromCurrency, string $toCurrency): ?string
    {
        return $this->cache->get("$fromCurrency$toCurrency", function (ItemInterface $item) use ($fromCurrency, $toCurrency): ?string {
            $item->expiresAfter(self::RATE_TTL);
            $query = [
                'get' => 'rates',
                'pairs' => "$fromCurrency$toCurrency",
                'date' => $this->getCurrentDateTime(),
                'key' => $this->apiKey,
            ];

            $data = $this->retrieveDataService->requestData($this->baseUrl, $query);
            if (!empty($data)) {
                if (!isset($data['data']["$fromCurrency$toCurrency"])) {
                    throw new RuntimeException('Can\'t retrieve information about this currency pair.');
                }

                return $data['data']["$fromCurrency$toCurrency"];
            }

            return null;
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function fetchCurrenciesList(): array
    {
        return $this->cache->get('currencies_list', function (): array {
            $currencies = $this->getCurrenciesRelations();

            return array_keys($currencies);
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function fetchCurrencyPairs(): array
    {
        return $this->cache->get('currency_pairs', function (): array {
            $query = [
                'get' => 'currency_list',
                'key' => $this->apiKey,
            ];
            $data = $this->retrieveDataService->requestData($this->baseUrl, $query);

            if (!empty($data['data'])) {

                return $data['data'];
            } else {
                throw new RuntimeException('Can\'t retrieve the currencies pairs.');
            }
        });
    }

    /**
     * @throws InvalidArgumentException
     */
    public function fetchAllRates(): array
    {
        $pairs = $this->fetchCurrencyPairs();
        $query = [
            'get' => 'rates',
            'pairs' => implode(',', $pairs),
            'date' => $this->getCurrentDateTime(),
            'key' => $this->apiKey,
        ];

        $data = $this->retrieveDataService->requestData($this->baseUrl, $query);
        if (!empty($data)) {
            if (empty($data['data'])) {
                throw new RuntimeException('Can\'t retrieve information about this currency pair.');
            }

            foreach ($data['data'] as $pair=>$rate) {
                // The main goal of this function is "warmup" caches during cache:warmup command implementation
                $this->cache->get($pair, function (ItemInterface $item) use ($rate): string {
                    $item->expiresAfter(self::RATE_TTL);

                    return $rate;
                });
            }

            return $data['data'];
        }

        return [];
    }

    /**
     * @throws InvalidArgumentException
     */
    private function getCurrenciesRelations(): array
    {
        return $this->cache->get('currencies_relations', function (): array {
            $currencyPairs = $this->fetchCurrencyPairs();
            $currencies = [];
            foreach ($currencyPairs as $pair) {
                $currencies[substr($pair, 0, 3)][] = substr($pair, 3, 3);
                $currencies[substr($pair, 3, 3)][] = substr($pair, 0, 3);
            }

            return $currencies;
        });
    }

    private function getCurrentDateTime(): string
    {
        $currentDateTime = new DateTimeImmutable();

        return $currentDateTime->format('Y-m-d\TH:i:s');
    }
}
