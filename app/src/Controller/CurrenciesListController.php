<?php

namespace App\Controller;

use App\Service\CurrenciesListFetcherService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class CurrenciesListController extends AbstractController
{
    private LoggerInterface $logger;

    private CurrenciesListFetcherService $listFetcherService;

    public function __construct(CurrenciesListFetcherService $listFetcherService, LoggerInterface $logger)
    {
        $this->listFetcherService = $listFetcherService;
        $this->logger = $logger;
    }

    #[Route('/api/v1/currencies-list', name: 'currencies_list', methods: ['GET'])]
    public function currenciesList(): Response
    {
        try {
            $list = $this->listFetcherService->fetchCurrenciesList();
        } catch (HttpException $e) {
            $this->logger->error('HttpException in CurrenciesListController: ' . $e->getMessage());

            return $this->json(['errors' => $e->getMessage()], $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logger->error('Exception in CurrenciesListController: ' . $e->getMessage());

            return $this->json(['errors' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json($list);
    }
}