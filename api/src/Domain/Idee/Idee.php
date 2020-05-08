<?php

declare(strict_types=1);

namespace App\Domain\Idee;

use App\Domain\Reference\Reference;
use App\Domain\Utilisateur\Utilisateur;

interface Idee extends Reference
{
    public function getDescription(): string;
    public function getAuteur(): Utilisateur;
    public function getDateProposition(): \DateTime;
}
