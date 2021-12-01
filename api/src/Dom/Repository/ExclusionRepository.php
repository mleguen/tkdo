<?php
declare(strict_types=1);

namespace App\Dom\Repository;

use App\Dom\Model\Occasion;
use App\Dom\Model\Exclusion;

interface ExclusionRepository
{
    /** @return Exclusion[] */
    public function readByParticipantsOccasion(Occasion $occasion): array;
}
