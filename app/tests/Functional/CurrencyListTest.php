<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CurrencyListTest extends WebTestCase
{
    public function testGetCurrenciesList()
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/currencies-list');

        $this->assertResponseIsSuccessful();
        $this->assertJson($client->getResponse()->getContent());
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertIsArray($data);
        $this->assertContains('USD', $data);
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
