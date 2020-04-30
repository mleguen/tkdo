<?php
declare(strict_types=1);

namespace App\Domain\Idee;

use App\Domain\DomainException\DomainRecordNotFoundException;

class IdeeInconnueException extends DomainRecordNotFoundException
{
    public $message = 'idée inconnue';
}
