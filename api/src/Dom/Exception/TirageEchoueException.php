<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class TirageEchoueException extends DomException
{
    public function __construct(string $message = "le tirage a échoué", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
