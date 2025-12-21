<?php
declare(strict_types=1);

namespace App\Appli\Exception;

class AuthPasDeTokenException extends AppliException
{
    public function __construct(string $message = "token d'authentification absent", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
