<?php

declare(strict_types=1);

namespace App\Appli\ModelAdaptor;

use App\Dom\Model\Auth;
use App\Dom\Model\Utilisateur;

class AuthAdaptor implements Auth
{
    /**
     * @param int[] $groupeIds
     */
    public static function fromUtilisateur(Utilisateur $utilisateur, array $groupeIds = []): AuthAdaptor
    {
        return new AuthAdaptor($utilisateur->getId(), $utilisateur->getAdmin(), $groupeIds);
    }

    /**
     * @param int[] $groupeIds
     */
    public function __construct(
        private readonly int $idUtilisateur,
        private readonly bool $admin,
        private readonly array $groupeIds = []
    ) {
    }

    #[\Override]
    public function estAdmin(): bool
    {
        return $this->admin;
    }

    #[\Override]
    public function estUtilisateur(Utilisateur $utilisateur): bool
    {
        return $this->idUtilisateur === $utilisateur->getId();
    }

    #[\Override]
    public function getIdUtilisateur(): int
    {
        return $this->idUtilisateur;
    }

    /**
     * @return int[]
     */
    #[\Override]
    public function getGroupeIds(): array
    {
        return $this->groupeIds;
    }
}
