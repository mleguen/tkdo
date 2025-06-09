<?php
declare(strict_types=1);

namespace App\Dom\Repository;

use App\Dom\Exception\OccasionInconnueException;
use App\Dom\Model\Occasion;
use App\Dom\Model\Utilisateur;
use DateTime;

interface OccasionRepository
{
    public function create(
        DateTime $date,
        string $titre
    ): Occasion;

    /**
     * @throws OccasionInconnueException
     */
    public function read(int $idOccasion): Occasion;

    /**
     * @return Occasion[]
     */
    public function readAll(): array;
    
    /**
     * @return Occasion[]
     */
    public function readByParticipant(Utilisateur $participant): array;
    
    public function update(Occasion $occasion): Occasion;
}
