<?php
declare(strict_types=1);

namespace App\Domain\Utilisateur;

interface UtilisateurRepository
{
    public function create(
        string $identifiant,
        string $email,
        string $mdp,
        string $nom,
        string $genre,
        bool $estAdmin,
        string $prefNotifIdees
    ): Utilisateur;

    /**
     * @throws UtilisateurNotFoundException
     */
    public function read(int $id, bool $reference = false): Utilisateur;

    /**
     * @throws UtilisateurNotFoundException
     * @return Utilisateur[]
     */
    public function readAll(): array;

    /**
     * Renvoie tous les utilisateurs souhaitant recevoir des notifications instantanées
     * pour les créations/suppressions d'idées,
     * et participant à au moins une occasion à venir avec l'utilisateur spécifié
     * (l'utilisateur en question et celui ayant effectué l'action exceptés).
     * 
     * @throws UtilisateurNotFoundException
     * @return Utilisateur[]
     */
    public function readAllByNotifInstantaneePourIdees(int $idUtilisateur, int $idActeur): array;

    /**
     * @throws UtilisateurNotFoundException
     */
    public function readOneByIdentifiant(string $identifiant): Utilisateur;

    /**
     * @throws UtilisateurNotFoundException
     */
    public function update(Utilisateur $utilisateur): Utilisateur;
}
