<?php
declare(strict_types=1);

namespace App\Domain\Occasion;

use App\Domain\DomainException\DomainRecordNotFoundException;

class OccasionNotFoundException extends DomainRecordNotFoundException
{
    public $message = 'occasion inconnue';
}
