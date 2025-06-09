<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class DoublonExclusionException extends DomException
{
    public $message = "l'exclusion existe déjà";
}
