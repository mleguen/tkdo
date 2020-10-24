<?php
declare(strict_types=1);

namespace App\Domain\Occasion;

use App\Domain\Utilisateur\Utilisateur;

interface Occasion
{
    public function getId(): int;
    public function getTitre(): string;
    /**
     * @return Utilisateur[]
     */
    public function getParticipants(): array;
    public function setTitre (string $titre): Occasion;
    /**
     * @param Utilisateur[] $participants
     */
    public function setParticipants (array $participants): Occasion;
}

