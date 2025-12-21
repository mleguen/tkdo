<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class OccasionPasseeException extends DomException
{
    public function __construct(string $message = "l'occasion est passée", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
