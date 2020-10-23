<?php
declare(strict_types=1);

namespace App\Application\Service;

class AuthPasDeBearerTokenException extends \Exception
{
    public $message = 'bearer token absent';
}
