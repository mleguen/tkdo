<?php
declare(strict_types=1);

namespace App\Appli\ModelAdaptor;

use App\Dom\Model\Auth;
use App\Dom\Model\Utilisateur;

class AuthAdaptor implements Auth
{
    private $idUtilisateur;
    private $admin;

    public static function fromUtilisateur(Utilisateur $utilisateur): AuthAdaptor
    {
        return new AuthAdaptor($utilisateur->getId(), $utilisateur->getAdmin());
    }

    public function __construct(
        int $idUtilisateur,
        bool $admin
    )
    {
        $this->idUtilisateur = $idUtilisateur;
        $this->admin = $admin;
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
