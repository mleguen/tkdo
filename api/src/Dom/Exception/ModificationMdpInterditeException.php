<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class ModificationMdpInterditeException extends DomException
{
    public $message = "seul l'utilisateur lui-même peut modifier son mot de passe";
}
