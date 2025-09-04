<?php

namespace App\Exceptions;

use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ValidationException extends \RuntimeException
{
    public function __construct(private readonly ConstraintViolationListInterface $violations)
    {
        parent::__construct('Validation failed');
    }

    public function toArray(): array
    {
        $out = [];
        foreach ($this->violations as $v) {
            $field = $v->getPropertyPath() ?: '_global';
            $out[$field][] = $v->getMessage();
        }

        return $out;
    }
}