<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class UtilisateurDejaParticipantException extends DomException
{
    public $message = "l'utilisateur participe déjà à l'occasion";
}
