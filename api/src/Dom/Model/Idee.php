<?php
declare(strict_types=1);

namespace App\Dom\Model;

use DateTime;

interface Idee
{
    public function getAuteur(): Utilisateur;
    public function getDateProposition(): DateTime;
    public function getDateSuppression(): ?DateTime;
    public function getDescription(): string;
    public function getId(): int;
    public function getUtilisateur(): Utilisateur;

    public function hasDateSuppression(): bool;

    public function setAuteur(Utilisateur $auteur): Idee;
    public function setDateProposition(DateTime $dateProposition): Idee;
    public function setDateSuppression(?DateTime $dateSuppression): Idee;
    public function setDescription(string $description): Idee;
    public function setUtilisateur(Utilisateur $utilisateur): Idee;
}
