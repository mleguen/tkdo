<?php

declare(strict_types=1);

namespace App\Dom\Model;

interface Auth
{
    public function estAdmin(): bool;
    public function estUtilisateur(Utilisateur $utilisateur): bool;
    public function getIdUtilisateur(): int;

    /**
     * @return int[]
     */
    public function getGroupeIds(): array;

    /**
     * @return int[]
     */
    public function getGroupeAdminIds(): array;
}
