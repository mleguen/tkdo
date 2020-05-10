<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Idee;

use App\Domain\Idee\Idee;
use App\Domain\Idee\IdeeInconnueException;
use App\Domain\Idee\IdeeRepository;
use App\Domain\Reference\Reference;
use App\Domain\Utilisateur\Utilisateur;
use App\Infrastructure\Persistence\Reference\DoctrineReferenceRepository;
use Doctrine\ORM\EntityManager;

class DoctrineIdeeRepository extends DoctrineReferenceRepository implements IdeeRepository
{
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, __NAMESPACE__ . '\DoctrineIdee');
    }

    /**
     * {@inheritdoc}
     */
    public function create(
        Utilisateur $utilisateur,
        string $description,
        Utilisateur $auteur,
        \DateTime $dateProposition
    ): Idee
    {
        $idee = (new DoctrineIdee())
            ->setUtilisateur($utilisateur)
            ->setDescription($description)
            ->setAuteur($auteur)
            ->setDateProposition($dateProposition);
        $this->em->persist($idee);
        $this->em->flush();
        return $idee;
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $id): Idee
    {
        $idee = $this->repository->find($id);
        if (is_null($idee)) throw new IdeeInconnueException();
        return $idee;
    }

    /**
     * {@inheritdoc}
     */
    public function readByUtilisateur(Utilisateur $utilisateur): array
    {
        return $this->repository->findBy([
            'utilisateur' => $utilisateur,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Reference $idee)
    {
        $this->em->remove($idee);
        $this->em->flush();
    }
}
