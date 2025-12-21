<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class TirageDejaLanceException extends DomException
{
    public function __construct(string $message = "des résultats existent déjà pour cette occasion", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
