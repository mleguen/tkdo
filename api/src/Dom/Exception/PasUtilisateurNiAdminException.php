<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class PasUtilisateurNiAdminException extends DomException
{
    public function __construct(string $message = "l'utilisateur authentifié n'est ni l'utilisateur lui-même, ni un administrateur", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
