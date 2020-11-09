<?php
declare(strict_types=1);

namespace App\Domain\Utilisateur;

interface Utilisateur
{
    public function getId(): int;

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
    public function getNom(): string;

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
}
