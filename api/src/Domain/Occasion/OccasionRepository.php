<?php

declare(strict_types=1);

namespace App\Domain\Occasion;

interface OccasionRepository
{
    /**
     * @return Occasion[]
     */
    public function findAll(): array;

    /**
     * @param int $id
     * @return Occasion
     * @throws OccasionNotFoundException
     */
    public function findOccasionOfId(int $id): Occasion;
}
