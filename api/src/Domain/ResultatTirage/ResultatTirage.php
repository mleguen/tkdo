<?php

declare(strict_types=1);

namespace App\Domain\ResultatTirage;

use App\Domain\Reference\Reference;
use App\Domain\Utilisateur\Utilisateur;

interface ResultatTirage extends Reference
{
  public function getQuiOffre(): Utilisateur;
  public function getQuiRecoit(): Utilisateur;
}
