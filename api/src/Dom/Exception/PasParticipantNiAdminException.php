<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class PasParticipantNiAdminException extends DomException
{
    public function __construct(string $message = "l'utilisateur authentifié ne participe pas à l'occasion et n'est pas un administrateur", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
