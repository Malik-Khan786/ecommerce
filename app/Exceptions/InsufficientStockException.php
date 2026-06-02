<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    public function __construct(string $productName, int $available)
    {
        parent::__construct(
            "Sorry, '{$productName}' only has {$available} unit(s) left in stock."
        );
    }
}
