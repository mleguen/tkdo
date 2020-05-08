<?php
declare(strict_types=1);

namespace App\Domain\Occasion;

use App\Domain\DomainException\DomainRecordNotFoundException;

class AucuneOccasionException extends DomainRecordNotFoundException
{
    public $message = 'aucune occasion';
}
