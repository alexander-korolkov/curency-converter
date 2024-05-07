<?php

namespace App\Factory;

use App\DTO\CurrencyConversionDTO;
use App\Service\CurrencyConverterService;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CurrencyConversionDTOFactory
{
    private ValidatorInterface $validator;

    private CurrencyConverterService $currencyConverterService;

    private LoggerInterface $logger;

    public function __construct(ValidatorInterface $validator, CurrencyConverterService $currencyConverterService, LoggerInterface $logger)
    {
        $this->validator = $validator;
        $this->currencyConverterService = $currencyConverterService;
        $this->logger = $logger;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function createFromRequest(Request $request): CurrencyConversionDTO
    {
        $conversionDTO = new CurrencyConversionDTO();
        $conversionDTO->from = $request->query->get('from');
        $conversionDTO->to = $request->query->get('to');
        $conversionDTO->amount = (float) $request->query->get('amount', 0);

        $violations = $this->validator->validate($conversionDTO);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                if (!isset($errors[$violation->getPropertyPath()])) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }
            }
            $this->logger->error('Validation error in CurrencyConversionDTOFactory: ' . json_encode($errors));
            throw new HttpException(Response::HTTP_BAD_REQUEST, json_encode(["Validation failed" => $errors]));
        }

        if ($conversionDTO->from === $conversionDTO->to) {
            $this->logger->notice('Someone sent the same currency codes');
            throw new HttpException(Response::HTTP_BAD_REQUEST, json_encode(["Validation failed" => 'You sent the same currency codes.']));
        }

        $conversionDTO->rate = $this->currencyConverterService->getRate($conversionDTO->from, $conversionDTO->to);
        $conversionDTO->convertedAmount = $this->currencyConverterService->convert($conversionDTO->from, $conversionDTO->to, $conversionDTO->amount);

        return $conversionDTO;
    }
}
