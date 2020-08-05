<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\ResultatTirage;

use App\Domain\Occasion\Occasion;
use App\Domain\ResultatTirage\ResultatTirageRepository;
use App\Infrastructure\Persistence\Occasion\DoctrineOccasion;
use Doctrine\ORM\EntityManager;

class DoctrineResultatTirageRepository implements ResultatTirageRepository
{
    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function readByOccasion(Occasion $occasion): array
    {
        return $this->em->getRepository(DoctrineResultatTirage::class)->findBy([
            'occasion' => $occasion,
        ]);
    }
}
