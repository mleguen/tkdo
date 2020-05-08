<?php
declare(strict_types=1);

namespace App\Domain\Utilisateur;

use App\Domain\Reference\ReferenceRepository;

interface UtilisateurRepository extends ReferenceRepository
{
    /**
     * @param int $id
     * @return Utilisateur
     * @throws UtilisateurInconnuException
     */
    public function read(int $id): Utilisateur;

    /**
     * @param string $identifiant
     * @param string $mdp
     * @return Utilisateur
     * @throws UtilisateurInconnuException
     */
    public function readOneByIdentifiants(string $identifiant, string $mdp): Utilisateur;

    /**
     * @param Utilisateur $utilisateur
     * @return Utilisateur
     * @throws UtilisateurInconnuException
     */
    public function update(Utilisateur $utilisateur): Utilisateur;
}
