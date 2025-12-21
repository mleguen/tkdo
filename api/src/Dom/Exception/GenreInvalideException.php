<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class GenreInvalideException extends DomException
{
    public function __construct(string $message = 'genre invalide', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
