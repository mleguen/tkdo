<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class PrefNotifIdeesInvalideException extends DomException
{
    public $message = 'format de préférence de notification incorrect';
}
