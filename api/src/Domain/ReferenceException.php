<?php
declare(strict_types=1);

namespace App\Domain;

use Error;

class ReferenceException extends Error
{
    public $message = 'seul getId() peut être appelée sur une référence';
}
