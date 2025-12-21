<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class PasParticipantException extends DomException
{
    public function __construct(string $message = "l'utilisateur ne participe pas à l'occasion", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
