<?php
declare(strict_types=1);

namespace App\Domain\Utilisateur;

interface UtilisateurRepository
{
    public function create(
        string $identifiant,
        string $mdp,
        string $nom,
        string $genre,
        bool $estAdmin
    ): Utilisateur;

    /**
     * @throws UtilisateurNotFoundException
     * @return Utilisateur[]
     */
    public function readAll(): array;

    /**
     * @throws UtilisateurNotFoundException
     */
    public function read(int $id, bool $reference = false): Utilisateur;

    /**
     * @throws UtilisateurNotFoundException
     */
    public function readOneByIdentifiant(string $identifiant): Utilisateur;

    /**
     * @throws UtilisateurNotFoundException
     */
    public function update(Utilisateur $utilisateur): Utilisateur;
}
