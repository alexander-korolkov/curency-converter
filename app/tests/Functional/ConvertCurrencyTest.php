<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ConvertCurrencyTest extends WebTestCase
{
    public function testCurrencyConversion()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/api/v1/convert?from=ETH&to=BTC&amount=3456');

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());
        $response = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals('ETH', $response['from']);
        $this->assertEquals('BTC', $response['to']);
        $this->assertEquals(3456, $response['amount']);
        $this->assertArrayHasKey('convertedAmount', $response);
        $this->assertArrayHasKey('rate', $response);

    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->restoreExceptionHandler();
    }

    protected function restoreExceptionHandler(): void
    {
        while (true) {
            $previousHandler = set_exception_handler(static fn() => null);
            restore_exception_handler();
            if ($previousHandler === null) {
                break;
            }
            restore_exception_handler();
        }
    }
}
