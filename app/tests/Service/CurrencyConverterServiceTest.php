<?php

namespace App\Tests\Service;

use App\Service\CurrenciesListFetcherService;
use App\Service\CurrencyConverterService;
use App\Service\CurrencyRateFetcherService;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\Cache\CacheInterface;

class CurrencyConverterServiceTest extends TestCase
{
    private MockObject $rateFetcherMock;
    private MockObject $cacheMock;
    private CurrencyConverterService $converter;
    private MockObject $logger;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->rateFetcherMock = $this->createMock(CurrencyRateFetcherService::class);
        $listFetcherMock = $this->createMock(CurrenciesListFetcherService::class);
        $this->cacheMock = $this->createMock(CacheInterface::class);
        $this->converter = new CurrencyConverterService(
            $this->rateFetcherMock,
            $listFetcherMock,
            $this->cacheMock,
            new NullLogger()
        );
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testConvert()
    {
        $from = 'USD';
        $to = 'EUR';
        $amount = '1000';
        $rate = '0.85767';

        $this->rateFetcherMock->method('fetchRate')
            ->willReturn($rate);

        $this->cacheMock->method('get')
            ->willReturn(true);

        $result = $this->converter->convert($from, $to, $amount);

        $this->assertMatchesRegularExpression('/^\d+\.\d{2}$/', $result);
    }
}
