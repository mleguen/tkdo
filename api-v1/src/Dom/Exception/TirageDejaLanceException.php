<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class TirageDejaLanceException extends DomException
{
    public $message = "des résultats existent déjà pour cette occasion";
}
