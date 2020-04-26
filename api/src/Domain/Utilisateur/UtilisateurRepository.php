<?php
declare(strict_types=1);

namespace App\Domain\Utilisateur;

interface UtilisateurRepository
{
    // /**
    //  * @return Utilisateur[]
    //  */
    // public function findAll(): array;

    /**
     * @param int $id
     * @return Utilisateur
     * @throws UtilisateurInconnuException
     */
    public function find(int $id): Utilisateur;

    /**
     * @param string $identifiant
     * @param string $mdp
     * @return Utilisateur
     * @throws UtilisateurInconnuException
     */
    public function findByIdentifiants(string $identifiant, string $mdp): Utilisateur;
}
