<?php
declare(strict_types=1);

namespace App\Domain\Resultat;

use App\Domain\Occasion\Occasion;

interface ResultatRepository
{
    /**
     * @param string $occasion
     * @return Resultat[]
     */
    public function readByOccasion(Occasion $occasion): array;
}
