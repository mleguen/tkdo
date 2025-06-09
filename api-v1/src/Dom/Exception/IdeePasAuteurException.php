<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class IdeePasAuteurException extends DomException
{
    public $message = "l'utilisateur authentifié n'est ni l'auteur de l'idée, ni un administrateur";
}
