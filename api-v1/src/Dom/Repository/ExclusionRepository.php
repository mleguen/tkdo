<?php
declare(strict_types=1);

namespace App\Dom\Repository;

use App\Dom\Exception\DoublonExclusionException;
use App\Dom\Model\Occasion;
use App\Dom\Model\Exclusion;
use App\Dom\Model\Utilisateur;

interface ExclusionRepository
{
    /** @throws DoublonExclusionException */
    public function create(
        Utilisateur $quiOffre,
        Utilisateur $quiNeDoitPasRecevoir
    ): Exclusion;

    /** @return Exclusion[] */
    public function readByQuiOffre(Utilisateur $quiOffre): array;

    /** @return Exclusion[] */
    public function readByParticipantsOccasion(Occasion $occasion): array;
}
