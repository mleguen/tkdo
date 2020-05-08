<?php
declare(strict_types=1);

namespace App\Domain\Occasion;

use App\Domain\Reference\ReferenceRepository;

interface OccasionRepository extends ReferenceRepository
{
    /**
     * @return Occasion
     * @throws AucuneOccasionException
     */
    public function readLast(): Occasion;
}
