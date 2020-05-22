<?php
declare(strict_types=1);

namespace App\Domain\ResultatTirage;

use App\Domain\Occasion\Occasion;

interface ResultatTirageRepository
{
    /**
     * @param string $occasion
     * @return ResultatTirage[]
     */
    public function readByOccasion(Occasion $occasion): array;
}
