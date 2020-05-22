<?php
declare(strict_types=1);

namespace App\Domain\Occasion;

interface OccasionRepository
{
    /**
     * @return Occasion
     * @throws AucuneOccasionException
     */
    public function readLast(): Occasion;
}
