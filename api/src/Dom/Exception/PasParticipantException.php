<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class PasParticipantException extends DomException
{
    public $message = "l'utilisateur ne participe pas à l'occasion";
}
