<?php
declare(strict_types=1);

namespace App\Dom\Repository;

use App\Dom\Model\Occasion;
use App\Dom\Model\Resultat;
use App\Dom\Model\Utilisateur;

interface ResultatRepository
{
    public function create(Occasion $occasion, Utilisateur $quiOffre, Utilisateur $quiRecoit): Resultat;

    /** @return Resultat[] */
    public function readByOccasion(Occasion $occasion): array;
}
