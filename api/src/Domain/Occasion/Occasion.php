<?php

declare(strict_types=1);

namespace App\Domain\Occasion;

use App\Domain\Reference\Reference;

interface Occasion extends Reference
{
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

