<?php

namespace App\Validator\Constraints;

use App\Service\CurrenciesListFetcherService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsValidCurrencyValidator extends ConstraintValidator
{
    private CurrenciesListFetcherService $currenciesListFetcherService;

    public function __construct(CurrenciesListFetcherService $currenciesListFetcherService)
    {
        $this->currenciesListFetcherService = $currenciesListFetcherService;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!in_array($value, $this->currenciesListFetcherService->fetchCurrenciesList()) && isset($constraint->message)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
