<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Utilisateur;

use App\Domain\Utilisateur\Utilisateur;
use App\Domain\Utilisateur\UtilisateurInconnuException;
use App\Domain\Utilisateur\UtilisateurRepository;
use App\Infrastructure\Persistence\Reference\DoctrineReferenceRepository;
use Doctrine\ORM\EntityManager;

class DoctrineUtilisateurRepository extends DoctrineReferenceRepository implements UtilisateurRepository
{
    public function __construct(EntityManager $em)
    {
        parent::__construct($em, __NAMESPACE__ . '\DoctrineUtilisateur');
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $id): Utilisateur
    {
        $utilisateur = $this->repository->find($id);
        if (is_null($utilisateur)) throw new UtilisateurInconnuException();
        return $utilisateur;
    }

    /**
     * {@inheritdoc}
     */
    public function readOneByIdentifiants(string $identifiant, string $mdp): Utilisateur
    {        
        $utilisateur = $this->repository->findOneBy([
            'identifiant' => $identifiant,
            'mdp' => $mdp
        ]);
        if (is_null($utilisateur)) throw new UtilisateurInconnuException();
        return $utilisateur;
    }

    /**
     * {@inheritdoc}
     */
    public function update(Utilisateur $utilisateur): Utilisateur
    {
        $this->em->persist($utilisateur);
        $this->em->flush();
        return $utilisateur;
    }
}
