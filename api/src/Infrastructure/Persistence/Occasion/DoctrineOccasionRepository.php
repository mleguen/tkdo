<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Occasion;

use App\Domain\Occasion\Occasion;
use App\Domain\Occasion\AucuneOccasionException;
use App\Domain\Occasion\OccasionRepository;
use Doctrine\ORM\EntityManager;

class DoctrineOccasionRepository implements OccasionRepository
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
