<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\ResultatTirage;

use App\Domain\Occasion\Occasion;
use App\Domain\ResultatTirage\ResultatTirageRepository;

class InMemoryResultatTirageRepository implements ResultatTirageRepository
{
    /**
     * @var DoctrineResultatTirage[]
     */
    private $resultats;

    public function __construct(array $resultats = [])
    {
        $this->resultats = $resultats;
    }

    /**
     * {@inheritdoc}
     */
    public function readByOccasion(Occasion $occasion): array
    {
        $idOccasion = $occasion->getId();
        return array_map(
            function ($rt) {
                return clone $rt;
            },
            array_values(
                array_filter($this->resultats, function ($rt) use ($idOccasion) {
                    return $rt->getOccasion()->getId() === $idOccasion;
                })
            )
        );
    }
}
