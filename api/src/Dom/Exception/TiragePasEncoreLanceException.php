<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class TiragePasEncoreLanceException extends DomException
{
    public $message = "le tirage n'a pas encore été lancé pour cette occasion";
}
