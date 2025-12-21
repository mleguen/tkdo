<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class DoublonExclusionException extends DomException
{
    public function __construct(string $message = "l'exclusion existe déjà", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
