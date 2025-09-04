<?php

namespace App\Services;

use App\Exceptions\ValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class DtoValidatorService
{
    public function __construct(private ValidatorInterface $validator) {}

    public function assertValid(object $dto): void
    {
        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            throw new ValidationException($violations);
        }
    }
}