<?php
declare(strict_types=1);

namespace App\Dom\Model;

interface Auth
{
    public function estAdmin(): bool;
    public function estUtilisateur(Utilisateur $utilisateur): bool;
}
