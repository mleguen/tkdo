<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class PrefNotifIdeesPasPeriodiqueException extends DomException
{
    public function __construct(string $message = "la préférence de notification spécifiée n'est pas périodique", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
