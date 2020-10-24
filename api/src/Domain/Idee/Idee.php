<?php
declare(strict_types=1);

namespace App\Domain\Idee;

use App\Domain\Utilisateur\Utilisateur;

interface Idee
{
    public function getId(): int;

    /**
     * @throws ReferenceException
     */
    public function getUtilisateur(): Utilisateur;

    /**
     * @throws ReferenceException
     */
    public function getDescription(): string;

    /**
     * @throws ReferenceException
     */
    public function getAuteur(): Utilisateur;

    /**
     * @throws ReferenceException
     */
    public function getDateProposition(): \DateTime;

    /**
     * @throws ReferenceException
     */
    public function setUtilisateur(Utilisateur $utilisateur): Idee;

    /**
     * @throws ReferenceException
     */
    public function setDescription(string $description): Idee;

    /**
     * @throws ReferenceException
     */
    public function setAuteur(Utilisateur $auteur): Idee;

    /**
     * @throws ReferenceException
     */
    public function setDateProposition(\DateTime $dateProposition): Idee;
}
