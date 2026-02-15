<?php

declare(strict_types=1);

namespace App\Appli\ModelAdaptor;

use App\Dom\Model\Auth;
use App\Dom\Model\Utilisateur;

class AuthAdaptor implements Auth
{
    /**
     * @param int[] $groupeIds
     * @param int[] $groupeAdminIds
     */
    public static function fromUtilisateur(Utilisateur $utilisateur, array $groupeIds = [], array $groupeAdminIds = []): AuthAdaptor
    {
        return new AuthAdaptor($utilisateur->getId(), $utilisateur->getAdmin(), $groupeIds, $groupeAdminIds);
    }

    /**
     * @param int[] $groupeIds
     * @param int[] $groupeAdminIds
     */
    public function __construct(
        private readonly int $idUtilisateur,
        private readonly bool $admin,
        private readonly array $groupeIds = [],
        private readonly array $groupeAdminIds = []
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

    /**
     * @return int[]
     */
    #[\Override]
    public function getGroupeAdminIds(): array
    {
        return $this->groupeAdminIds;
    }
}
