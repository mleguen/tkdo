<?php
declare(strict_types=1);

namespace App\Application\Service;

class AuthPasDeTokenException extends \Exception
{
    public $message = "token d'authentification absent";
}
