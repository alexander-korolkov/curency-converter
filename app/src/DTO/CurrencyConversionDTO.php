<?php

namespace App\DTO;

use App\Validator\Constraints\IsValidCurrency as IsValidCurrency;
use Symfony\Component\Validator\Constraints as Assert;

class CurrencyConversionDTO
{
    #[Assert\NotBlank]
    #[Assert\Length(exactly: 3)]
    #[Assert\Type("string")]
    #[Assert\Regex("/^[A-Z]+$/")]
    #[IsValidCurrency]
    public string $from;

    #[Assert\NotBlank]
    #[Assert\Length(exactly: 3)]
    #[Assert\Type("string")]
    #[Assert\Regex("/^[A-Z]+$/")]
    #[IsValidCurrency]
    public string $to;

    #[Assert\NotBlank]
    #[Assert\Type("numeric")]
    #[Assert\Positive]
    public float $amount;

    public string $convertedAmount;

    public string $rate;

    public function toArray(): array
    {
        return [
            'from' => $this->from,
            'to' => $this->to,
            'amount' => $this->amount,
            'convertedAmount' => $this->convertedAmount,
            'rate' => $this->rate,
        ];
    }
}
