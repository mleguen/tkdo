<?php
declare(strict_types=1);

namespace App\Domain\ResultatTirage;

use App\Domain\Occasion\Occasion;
use App\Domain\Reference\ReferenceRepository;

interface ResultatTirageRepository extends ReferenceRepository
{
    /**
     * @param string $occasion
     * @return ResultatTirage[]
     */
    public function readByOccasion(Occasion $occasion): array;
}
