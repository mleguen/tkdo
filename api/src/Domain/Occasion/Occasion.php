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
}

