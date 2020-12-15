<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class PasParticipantNiAdminException extends DomException
{
    public $message = "l'utilisateur authentifié ne participe pas à l'occasion et n'est pas un administrateur";
}
