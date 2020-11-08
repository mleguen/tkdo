<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Occasion;

use App\Domain\Occasion\Occasion;
use App\Domain\Occasion\OccasionNotFoundException;
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
    public function readLastByParticipant(int $idParticipant): Occasion
    {
        $occasion = $this->em->createQueryBuilder()
            ->select('o')
            ->from(DoctrineOccasion::class, 'o')
            ->where(':idParticipant MEMBER OF o.participants')
            ->orderBy('o.id', 'DESC')
            ->setParameter('idParticipant', $idParticipant)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        if (is_null($occasion)) throw new OccasionNotFoundException();
        return $occasion;
    }
}
