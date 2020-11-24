<?php
declare(strict_types=1);

namespace App\Domain\Occasion;

interface OccasionRepository
{
    /**
     * @throws OccasionNotFoundException
     */
    public function read(int $idOccasion): Occasion;

    /**
     * @return Occasion[]
     * @throws OccasionNotFoundException
     */
    public function readByParticipant(int $idParticipant): array;
}
