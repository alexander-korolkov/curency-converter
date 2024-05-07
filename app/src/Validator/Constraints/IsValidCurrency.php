<?php

namespace App\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class IsValidCurrency extends Constraint
{
    public string $message = 'The currency code "{{ string }}" is not valid.';
}
