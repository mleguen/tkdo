<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class IdeeDejaSupprimeeException extends DomException
{
    public $message = "l'idée a déjà été marquée comme supprimée";
}
