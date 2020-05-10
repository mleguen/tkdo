<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\ResultatTirage;

use App\Domain\Occasion\Occasion;
use App\Domain\ResultatTirage\ResultatTirageRepository;
use App\Infrastructure\Persistence\Reference\DoctrineReferenceRepository;
use Doctrine\ORM\EntityManager;

class DoctrineResultatTirageRepository extends DoctrineReferenceRepository implements ResultatTirageRepository
{
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, __NAMESPACE__ . '\DoctrineResultatTirage');
    }

    /**
     * {@inheritdoc}
     */
    public function readByOccasion(Occasion $occasion): array
    {
        return $this->repository->findBy([
            'occasion' => $occasion,
        ]);
    }
}
