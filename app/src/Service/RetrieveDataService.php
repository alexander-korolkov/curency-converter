<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class RetrieveDataService
{
    private HttpClientInterface $client;

    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function requestData(string $url, array $query): array
    {
        try {
            $response = $this->client->request('GET', $url, [
                'query' => $query,
            ]);

            $data = $response->toArray();
        } catch (ClientExceptionInterface $e) {
            // Error 4xx
            $statusCode = $this->getStatusCode($e);
            $statusCode = $statusCode ?? Response::HTTP_BAD_REQUEST;
            $this->logger->error("Client error with status code $statusCode in RetrieveDataService: " . $e->getMessage());
            throw new HttpException($statusCode, 'Client error: ' . $e->getMessage());
        } catch (ServerExceptionInterface $e) {
            // Error 5xx
            $statusCode = $this->getStatusCode($e);
            $statusCode = $statusCode ?? Response::HTTP_INTERNAL_SERVER_ERROR;
            $this->logger->error("Server error with status code $statusCode in RetrieveDataService: " . $e->getMessage());
            throw new HttpException($statusCode, 'Server error: ' . $e->getMessage());
        } catch (RedirectionExceptionInterface $e) {
            $statusCode = $this->getStatusCode($e);
            $statusCode = $statusCode ?? Response::HTTP_TEMPORARY_REDIRECT;
            $this->logger->error("Redirection error with status code $statusCode in RetrieveDataService: " . $e->getMessage());
            throw new HttpException($statusCode, 'Redirection error: ' . $e->getMessage());
        } catch (TransportExceptionInterface $e) {
            $this->logger->error("Transport error in RetrieveDataService: " . $e->getMessage());
            throw new HttpException(Response::HTTP_SERVICE_UNAVAILABLE, 'Transport error: ' . $e->getMessage());
        } catch (Throwable $e) {
            $this->logger->error("Error in RetrieveDataService: " . $e->getMessage());
            throw new RuntimeException('Error fetching data: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $data;
    }

    private function getStatusCode($e): ?int
    {
        try {

            return $e->getResponse() ? $e->getResponse()->getStatusCode() : null;
        } catch (TransportExceptionInterface $transportException) {
            $this->logger->error('Can\'t get the status code: ' . $transportException->getMessage());
        }

        return null;
    }
}