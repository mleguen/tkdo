<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class IdentifiantDejaUtiliseException extends DomException
{
    public function __construct(string $message = 'identifiant déjà utilisé', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
