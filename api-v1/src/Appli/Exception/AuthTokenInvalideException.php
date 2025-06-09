<?php
declare(strict_types=1);

namespace App\Appli\Exception;

class AuthTokenInvalideException extends AppliException
{
    public $message = "token d'authentification invalide";
}
