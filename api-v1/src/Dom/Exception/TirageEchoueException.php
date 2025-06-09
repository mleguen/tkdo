<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class TirageEchoueException extends DomException
{
    public $message = "le tirage a échoué";
}
