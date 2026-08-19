<?php

namespace App\Exceptions\Gameplay;

use RuntimeException;

class GameplayException extends RuntimeException
{
    public function __construct(string $message, private int $statusCode = 422, private array $extra = [])
    {
        parent::__construct($message);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function extra(): array
    {
        return $this->extra;
    }
}
