<?php

declare(strict_types=1);

namespace App\Domain\Connexion;

use App\Domain\DomainException\DomainRecordNotFoundException;

class ConnexionNotFoundException extends DomainRecordNotFoundException
{
    public $message = 'The connexion you requested does not exist.';
}
