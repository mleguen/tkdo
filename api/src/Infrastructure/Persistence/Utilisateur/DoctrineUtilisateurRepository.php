<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Utilisateur;

use App\Domain\Utilisateur\Utilisateur;
use App\Domain\Utilisateur\UtilisateurInconnuException;
use App\Domain\Utilisateur\UtilisateurRepository;
use Doctrine\ORM\EntityManager;

class DoctrineUtilisateurRepository implements UtilisateurRepository
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
    public function read(int $id, bool $reference = false): Utilisateur
    {
        if ($reference) return $this->em->getReference(DoctrineUtilisateur::class, $id);
        $utilisateur = $this->em->getRepository(DoctrineUtilisateur::class)->find($id);
        if (is_null($utilisateur)) throw new UtilisateurInconnuException();
        return $utilisateur;
    }

    /**
     * {@inheritdoc}
     */
    public function readOneByIdentifiants(string $identifiant, string $mdp): Utilisateur
    {        
        /**
         * @var DoctrineUtilisateur
         */
        $utilisateur = $this->em->getRepository(DoctrineUtilisateur::class)->findOneBy([
            'identifiant' => $identifiant
        ]);
        if (
            is_null($utilisateur) ||
            !password_verify($mdp, $utilisateur->getMdp())
        ) {
            throw new UtilisateurInconnuException();
        }
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
