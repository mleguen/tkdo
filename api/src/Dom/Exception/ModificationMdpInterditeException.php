<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class ModificationMdpInterditeException extends DomException
{
    public function __construct(string $message = "seul l'utilisateur lui-même peut modifier son mot de passe", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
