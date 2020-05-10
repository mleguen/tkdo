<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Occasion;

use App\Domain\Occasion\Occasion;
use App\Domain\Occasion\AucuneOccasionException;
use App\Domain\Occasion\OccasionRepository;
use App\Infrastructure\Persistence\Reference\DoctrineReferenceRepository;
use Doctrine\ORM\EntityManager;

class DoctrineOccasionRepository extends DoctrineReferenceRepository implements OccasionRepository
{
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, __NAMESPACE__ . '\DoctrineOccasion');
    }

    /**
     * {@inheritdoc}
     */
    public function readLast(): Occasion
    {
        $occasion = $this->em->createQueryBuilder()
            ->select('e')
            ->from($this->entityName, 'e')
            ->orderBy('e.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        if (is_null($occasion)) throw new AucuneOccasionException();
        return $occasion;
    }
}
