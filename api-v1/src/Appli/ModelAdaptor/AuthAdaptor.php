<?php
declare(strict_types=1);

namespace App\Appli\ModelAdaptor;

use App\Dom\Model\Auth;
use App\Dom\Model\Utilisateur;

class AuthAdaptor implements Auth
{
    public static function fromUtilisateur(Utilisateur $utilisateur): AuthAdaptor
    {
        return new AuthAdaptor($utilisateur->getId(), $utilisateur->getAdmin());
    }

    public function __construct(private readonly int $idUtilisateur, private readonly bool $admin)
    {
    }

    public function estAdmin(): bool
    {
        return $this->admin;
    }

    public function estUtilisateur(Utilisateur $utilisateur): bool
    {
        return $this->idUtilisateur === $utilisateur->getId();
    }

    public function getIdUtilisateur(): int
    {
        return $this->idUtilisateur;
    }
}
