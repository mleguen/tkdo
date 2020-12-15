<?php
declare(strict_types=1);

namespace App\Dom\Repository;

use App\Dom\Exception\IdentifiantDejaUtiliseException;
use App\Dom\Exception\UtilisateurInconnuException;
use App\Dom\Model\Utilisateur;
use DateTime;

interface UtilisateurRepository
{
    /**
     * @throws IdentifiantDejaUtiliseException
     */
    public function create(
        string $identifiant,
        string $email,
        string $mdpClair,
        string $nom,
        string $genre,
        bool $admin,
        string $prefNotifIdees,
        DateTime $dateDerniereNotifPeriodique
    ): Utilisateur;

    /**
     * @throws UtilisateurInconnuException
     */
    public function read(int $id, bool $reference = false): Utilisateur;

    /**
     * @throws UtilisateurInconnuException
     * @return Utilisateur[]
     */
    public function readAll(): array;

    /**
     * Renvoie tous les utilisateurs souhaitant recevoir des notifications instantanées
     * pour les créations/suppressions d'idées,
     * et participant à au moins une occasion à venir avec l'utilisateur spécifié
     * (l'utilisateur en question excepté).
     * 
     * @throws UtilisateurInconnuException
     * @return Utilisateur[]
     */
    public function readAllByNotifInstantaneePourIdees(Utilisateur $utilisateur): array;

    /**
     * Renvoie tous les utilisateurs souhaitant recevoir la notification périodique spécifiée
     * ayant n'ayant pas été notifiés depuis la date spécifiée (ou jamais encore notifiés)
     * 
     * @return Utilisateur[]
     */
    public function readAllByNotifPeriodique(string $prefNotifIdees, DateTime $dateMaxDerniereNotifPeriodique): array;

    /**
     * @throws UtilisateurInconnuException
     */
    public function readOneByIdentifiant(string $identifiant): Utilisateur;

    /**
     * @throws UtilisateurInconnuException
     * @throws IdentifiantDejaUtiliseException
     */
    public function update(Utilisateur $utilisateur): Utilisateur;
}
