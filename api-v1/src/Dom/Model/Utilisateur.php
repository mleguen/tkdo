<?php
declare(strict_types=1);

namespace App\Dom\Model;

use DateTime;

interface Utilisateur
{
    public function estUtilisateur(Utilisateur $utilisateur): bool;
    
    public function getAdmin(): bool;
    public function getDateDerniereNotifPeriodique(): DateTime;
    public function getEmail(): string;
    public function getGenre(): string;
    public function getId(): int;
    public function getIdentifiant(): string;
    /** @return string|null */
    public function getMdpClair(): string;
    public function getNom(): string;
    /** @return Occasion[] */
    public function getOccasions(): array;
    public function getPrefNotifIdees(): string;

    public function setAdmin(bool $admin): Utilisateur;
    public function setDateDerniereNotifPeriodique(DateTime $dateDerniereNotifPeriodique): Utilisateur;
    public function setEmail(string $email): Utilisateur;
    public function setGenre(string $genre): Utilisateur;
    public function setIdentifiant(string $identifiant): Utilisateur;
    public function setMdpClair(string $mdpClair): Utilisateur;
    public function setNom(string $nom): Utilisateur;
    public function setPrefNotifIdees(string $prefNotifIdees): Utilisateur;

    public function verifieMdp(string $mdpClair): bool;
}
