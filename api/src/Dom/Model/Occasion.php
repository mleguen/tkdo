<?php
declare(strict_types=1);

namespace App\Dom\Model;

use DateTime;

interface Occasion
{
    public function addParticipant(Utilisateur $participant): Occasion;
    public function getDate(): DateTime;
    public function getId(): int;
    public function getTitre(): string;
    /** @return Utilisateur[] */
    public function getParticipants(): array;
    public function setDate (DateTime $date): Occasion;
    public function setTitre (string $titre): Occasion;
    /** @param Utilisateur[] $participants */
    public function setParticipants (array $participants): Occasion;
}

