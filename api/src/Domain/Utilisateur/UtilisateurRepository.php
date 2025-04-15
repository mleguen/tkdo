<?php

declare(strict_types=1);

namespace App\Domain\Utilisateur;

interface UtilisateurRepository
{
    /**
     * @return Utilisateur[]
     */
    public function findAll(): array;

    /**
     * @param int $id
     * @return Utilisateur
     * @throws UtilisateurNotFoundException
     */
    public function findUtilisateurOfId(int $id): Utilisateur;
}
