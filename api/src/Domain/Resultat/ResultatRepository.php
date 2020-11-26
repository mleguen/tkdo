<?php
declare(strict_types=1);

namespace App\Domain\Resultat;

use App\Domain\Occasion\Occasion;
use App\Domain\Utilisateur\Utilisateur;

interface ResultatRepository
{
    public function create(Occasion $occasion, Utilisateur $quiOffre, Utilisateur $quiRecoit): Resultat;

    /** @return Resultat[] */
    public function readByOccasion(Occasion $occasion): array;
}
