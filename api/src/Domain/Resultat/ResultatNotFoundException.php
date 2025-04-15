<?php

declare(strict_types=1);

namespace App\Domain\Resultat;

use App\Domain\DomainException\DomainRecordNotFoundException;

class ResultatNotFoundException extends DomainRecordNotFoundException
{
    public $message = 'The resultat you requested does not exist.';
}
