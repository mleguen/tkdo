<?php

namespace App\Repository;

use App\Entity\Exclusion;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Exclusion>
 */
class ExclusionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Exclusion::class);
    }

    /**
     * Find all exclusions for a given user (where user is either utilisateur1 or utilisateur2)
     *
     * @param Utilisateur $utilisateur
     * @return Exclusion[]
     */
    public function findByUtilisateur(Utilisateur $utilisateur): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.utilisateur1 = :utilisateur OR e.utilisateur2 = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find an existing exclusion between two users (regardless of order)
     *
     * @param Utilisateur $utilisateur1
     * @param Utilisateur $utilisateur2
     * @return Exclusion|null
     */
    public function findExistingExclusion(Utilisateur $utilisateur1, Utilisateur $utilisateur2): ?Exclusion
    {
        return $this->createQueryBuilder('e')
            ->where('(e.utilisateur1 = :u1 AND e.utilisateur2 = :u2) OR (e.utilisateur1 = :u2 AND e.utilisateur2 = :u1)')
            ->setParameter('u1', $utilisateur1)
            ->setParameter('u2', $utilisateur2)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find all exclusions
     *
     * @return Exclusion[]
     */
    public function findAllExclusions(): array
    {
        return $this->findAll();
    }
}
