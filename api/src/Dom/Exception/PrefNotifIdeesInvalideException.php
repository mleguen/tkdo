<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class PrefNotifIdeesInvalideException extends DomException
{
    public function __construct(string $message = 'format de préférence de notification incorrect', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
