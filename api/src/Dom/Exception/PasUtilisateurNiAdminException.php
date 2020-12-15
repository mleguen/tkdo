<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class PasUtilisateurNiAdminException extends DomException
{
    public $message = "l'utilisateur authentifié n'est ni l'utilisateur lui-même, ni un administrateur";
}
