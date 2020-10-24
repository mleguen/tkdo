<?php

declare(strict_types=1);

namespace App\Domain\Resultat;

use App\Domain\Occasion\Occasion;
use App\Domain\Utilisateur\Utilisateur;

interface Resultat
{
  public function getQuiOffre(): Utilisateur;
  public function getQuiRecoit(): Utilisateur;
  public function setOccasion(Occasion $occasion): Resultat;
  public function setQuiOffre(Utilisateur $quiOffre): Resultat;
  public function setQuiRecoit(Utilisateur $quiRecoit): Resultat;
}
