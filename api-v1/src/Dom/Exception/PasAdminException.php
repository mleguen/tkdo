<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class PasAdminException extends DomException
{
    public $message = "l'utilisateur authentifié n'est pas un administrateur";
}
