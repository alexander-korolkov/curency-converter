<?php

namespace App\Controller;

use App\Factory\CurrencyConversionDTOFactory;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class CurrencyConverterController extends AbstractController
{
    private LoggerInterface $logger;

    private CurrencyConversionDTOFactory $factory;

    public function __construct(CurrencyConversionDTOFactory $factory, LoggerInterface $logger)
    {
        $this->factory = $factory;
        $this->logger = $logger;
    }

    #[Route('/api/v1/convert', name: 'convert_currency', methods: ['GET'])]
    public function convert(Request $request): Response
    {
        try {
            $conversionDTO = $this->factory->createFromRequest($request);
        } catch (HttpException $e) {
            $message = json_decode($e->getMessage());
            $message = $message ?? $e->getMessage();
            $this->logger->error('HttpException in CurrencyConverterController: ' . $e->getMessage());

            return $this->json(['errors' => $message], $e->getStatusCode());
        } catch (Throwable $e) {
            $this->logger->error('Exception in CurrencyConverterController: ' . $e->getMessage());

            return $this->json(['errors' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->json($conversionDTO->toArray());
    }
}
