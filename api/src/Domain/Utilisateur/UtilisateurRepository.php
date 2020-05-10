<?php
declare(strict_types=1);

namespace App\Domain\Utilisateur;

use App\Domain\Reference\ReferenceRepository;

interface UtilisateurRepository extends ReferenceRepository
{
    /**
     * @throws UtilisateurInconnuException
     */
    public function read(int $id): Utilisateur;

    /**
     * @throws UtilisateurInconnuException
     */
    public function readOneByIdentifiants(string $identifiant, string $mdp): Utilisateur;

    /**
     * @throws UtilisateurInconnuException
     */
    public function update(Utilisateur $utilisateur): Utilisateur;
}
