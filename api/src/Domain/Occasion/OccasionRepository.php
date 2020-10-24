<?php
declare(strict_types=1);

namespace App\Domain\Occasion;

interface OccasionRepository
{
    /**
     * @return Occasion
     * @throws OccasionNotFoundException
     */
    public function readLast(): Occasion;
}
