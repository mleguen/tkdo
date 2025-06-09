<?php

namespace App\Repository;

use App\Entity\Idee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Idee>
 */
class IdeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Idee::class);
    }

    /**
     * Find all non-deleted ideas
     *
     * @return Idee[]
     */
    public function findNonDeleted(): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.supprimee = :supprimee')
            ->setParameter('supprimee', false)
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
