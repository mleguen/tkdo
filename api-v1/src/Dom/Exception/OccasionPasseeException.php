<?php
declare(strict_types=1);

namespace App\Dom\Exception;

class OccasionPasseeException extends DomException
{
    public $message = "l'occasion est passée";
}
