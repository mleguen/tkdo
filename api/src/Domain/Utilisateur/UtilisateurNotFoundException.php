<?php
declare(strict_types=1);

namespace App\Domain\Utilisateur;

use App\Domain\DomainException\DomainRecordNotFoundException;

class UtilisateurNotFoundException extends DomainRecordNotFoundException
{
    public $message = 'utilisateur inconnu';
}
