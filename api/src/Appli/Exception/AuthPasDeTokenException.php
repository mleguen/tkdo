<?php
declare(strict_types=1);

namespace App\Appli\Exception;

class AuthPasDeTokenException extends AppliException
{
    public $message = "token d'authentification absent";
}
