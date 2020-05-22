<?php
declare(strict_types=1);

namespace App\Domain\Utilisateur;

interface UtilisateurRepository
{
    /**
     * @throws UtilisateurInconnuException
     */
    public function read(int $id, bool $reference = false): Utilisateur;

    /**
     * @throws UtilisateurInconnuException
     */
    public function readOneByIdentifiants(string $identifiant, string $mdp): Utilisateur;

    /**
     * @throws UtilisateurInconnuException
     */
    public function update(Utilisateur $utilisateur): Utilisateur;
}
