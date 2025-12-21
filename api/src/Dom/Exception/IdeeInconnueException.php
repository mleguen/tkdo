<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class IdeeInconnueException extends DomException
{
    public function __construct(string $message = 'idée inconnue', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
