<?php

declare(strict_types=1);

namespace App\Domain\ResultatTirage;

use App\Domain\Occasion\Occasion;
use App\Domain\Reference\Reference;
use App\Domain\Utilisateur\Utilisateur;

interface ResultatTirage extends Reference
{
  public function getQuiOffre(): Utilisateur;
  public function getQuiRecoit(): Utilisateur;
  public function setOccasion(Occasion $occasion): ResultatTirage;
  public function setQuiOffre(Utilisateur $quiOffre): ResultatTirage;
  public function setQuiRecoit(Utilisateur $quiRecoit): ResultatTirage;
}
