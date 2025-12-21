<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class UtilisateurOffreOuRecoitDejaException extends DomException
{
    public function __construct(string $message = "l'un des utilisateurs offre ou reçoit déjà pour cette occasion", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
