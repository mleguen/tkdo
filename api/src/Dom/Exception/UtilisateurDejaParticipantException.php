<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class UtilisateurDejaParticipantException extends DomException
{
    public function __construct(string $message = "l'utilisateur participe déjà à l'occasion", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
