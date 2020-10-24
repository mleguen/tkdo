<?php
declare(strict_types=1);

namespace App\Domain\Utilisateur;

interface UtilisateurRepository
{
    /**
     * @throws UtilisateurNotFoundException
     */
    public function read(int $id, bool $reference = false): Utilisateur;

    /**
     * @throws UtilisateurNotFoundException
     */
    public function readOneByIdentifiants(string $identifiant, string $mdp): Utilisateur;

    /**
     * @throws UtilisateurNotFoundException
     */
    public function update(Utilisateur $utilisateur): Utilisateur;
}
