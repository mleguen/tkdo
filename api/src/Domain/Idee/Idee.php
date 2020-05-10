<?php

declare(strict_types=1);

namespace App\Domain\Idee;

use App\Domain\Reference\Reference;
use App\Domain\Utilisateur\Utilisateur;

interface Idee extends Reference
{
    public function getDescription(): string;
    public function getAuteur(): Utilisateur;
    public function getDateProposition(): \DateTime;
    public function setUtilisateur(Utilisateur $utilisateur): Idee;
    public function setDescription(string $description): Idee;
    public function setAuteur(Utilisateur $auteur): Idee;
    public function setDateProposition(\DateTime $dateProposition): Idee;
}
