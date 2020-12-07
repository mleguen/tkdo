<?php
declare(strict_types=1);

namespace App\Domain\Utilisateur;

interface Utilisateur
{
    public function getId(): int;

    /**
     * @throws ReferenceException
     */
    public function getEmail(): string;

    /**
     * @throws ReferenceException
     */
    public function getEstAdmin(): bool;

    /**
     * @throws ReferenceException
     */
    public function getGenre(): string;

    /**
     * @throws ReferenceException
     */
    public function getIdentifiant(): string;

    /**
     * @throws ReferenceException
     */
    public function getMdp(): string;

    /**
     * @throws ReferenceException
     */
    public function getNom(): string;

    /**
     * @throws ReferenceException
     */
    public function getPrefNotifIdees(): string;

    /**
     * @throws ReferenceException
     */
    public function setEmail(string $email): Utilisateur;

    /**
     * @throws ReferenceException
     */
    public function setEstAdmin(bool $estAdmin): Utilisateur;

    /**
     * @throws ReferenceException
     */
    public function setGenre(string $genre): Utilisateur;

    /**
     * @throws ReferenceException
     */
    public function setIdentifiant(string $identifiant): Utilisateur;

    /**
     * @throws ReferenceException
     */
    public function setMdp(string $mdp): Utilisateur;

    /**
     * @throws ReferenceException
     */
    public function setNom(string $nom): Utilisateur;

    /**
     * @throws ReferenceException
     */
    public function setPrefNotifIdees(string $prefNotifIdees): Utilisateur;
}
