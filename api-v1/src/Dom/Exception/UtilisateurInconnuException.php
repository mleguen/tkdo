<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class UtilisateurInconnuException extends DomException
{
    public $message = 'utilisateur inconnu';
}
