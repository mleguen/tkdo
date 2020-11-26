<?php
declare(strict_types=1);

namespace App\Domain\Occasion;

interface OccasionRepository
{
    public function create(string $titre): Occasion;

    /**
     * @throws OccasionNotFoundException
     */
    public function read(int $idOccasion): Occasion;

    /**
     * @return Occasion[]
     */
    public function readAll(): array;
    
    /**
     * @return Occasion[]
     */
    public function readByParticipant(int $idParticipant): array;
}
