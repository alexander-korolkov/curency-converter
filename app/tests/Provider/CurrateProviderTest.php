<?php

namespace App\Tests\Provider;

use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use App\Provider\CurrateProvider;
use App\Service\RetrieveDataService;
use Psr\Cache\InvalidArgumentException;
use RuntimeException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CurrateProviderTest extends TestCase
{
    private MockObject $retrieveDataServiceMock;
    private MockObject $cacheMock;
    private MockObject $cacheItemMock;
    private CurrateProvider $currateProvider;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->retrieveDataServiceMock = $this->createMock(RetrieveDataService::class);
        $this->cacheMock = $this->createMock(CacheInterface::class);
        $this->cacheItemMock = $this->createMock(ItemInterface::class);

        $this->currateProvider = new CurrateProvider(
            $this->retrieveDataServiceMock,
            'fake-api-key',
            'http://fake-url.com',
            $this->cacheMock
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testFetchRateCache()
    {
        $fromCurrency = 'USD';
        $toCurrency = 'EUR';
        $expectedRate = '0.84';

        $this->cacheMock->method('get')
            ->with($this->equalTo($fromCurrency . $toCurrency))
            ->willReturnCallback(function ($key, $callback) use ($expectedRate) {
                return $expectedRate;
            });

        $rate = $this->currateProvider->fetchRate($fromCurrency, $toCurrency);
        $this->assertEquals($expectedRate, $rate);
    }

    public function testFetchRate()
    {
        $fromCurrency = 'USD';
        $toCurrency = 'EUR';
        $expectedRate = '0.84';
        $response = ['data' => [$fromCurrency . $toCurrency => $expectedRate]];

        $this->cacheMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo($fromCurrency . $toCurrency))
            ->willReturnCallback(function ($key, $callback) use ($response) {
                return $callback($this->cacheItemMock);
            });

        $this->retrieveDataServiceMock->method('requestData')
            ->willReturn($response);

        $rate = $this->currateProvider->fetchRate($fromCurrency, $toCurrency);
        $this->assertEquals($expectedRate, $rate);
    }
}
