<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class IdentifiantDejaUtiliseException extends DomException
{
    public $message = 'identifiant déjà utilisé';
}
