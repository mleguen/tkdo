<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class UtilisateurOffreOuRecoitDejaException extends DomException
{
    public $message = "l'un des utilisateurs offre ou reçoit déjà pour cette occasion";
}
