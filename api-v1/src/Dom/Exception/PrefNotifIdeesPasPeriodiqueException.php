<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class PrefNotifIdeesPasPeriodiqueException extends DomException
{
    public $message = "la préférence de notification spécifiée n'est pas périodique";
}
