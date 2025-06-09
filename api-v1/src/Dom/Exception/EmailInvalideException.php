<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class EmailInvalideException extends DomException
{
    public function __construct(string $email)
    {
        parent::__construct("$email n'est pas un email valide");
    }
}
