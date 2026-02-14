<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class GroupeInconnuException extends DomException
{
    public function __construct(string $message = 'groupe inconnu', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
