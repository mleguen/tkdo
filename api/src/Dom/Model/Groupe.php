<?php
declare(strict_types=1);

namespace App\Dom\Model;

use DateTime;

interface Groupe
{
    public function getId(): int;
    public function getNom(): string;
    public function getArchive(): bool;
    public function getDateCreation(): DateTime;
    /** @return Appartenance[] */
    public function getAppartenances(): array;

    public function setNom(string $nom): Groupe;
    public function setArchive(bool $archive): Groupe;
    public function setDateCreation(DateTime $dateCreation): Groupe;
    public function addAppartenance(Appartenance $appartenance): Groupe;
}
