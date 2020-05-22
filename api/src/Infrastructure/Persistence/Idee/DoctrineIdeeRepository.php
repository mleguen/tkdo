<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Idee;

use App\Domain\Idee\Idee;
use App\Domain\Idee\IdeeInconnueException;
use App\Domain\Idee\IdeeRepository;
use App\Domain\Utilisateur\Utilisateur;
use Doctrine\ORM\EntityManager;

class DoctrineIdeeRepository implements IdeeRepository
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
    public function read(int $id, bool $reference = false): Idee
    {
        if ($reference) return $this->em->getReference(DoctrineIdee::class, $id);
        $idee = $this->em->getRepository(DoctrineIdee::class)->find($id);
        if (is_null($idee)) throw new IdeeInconnueException();
        return $idee;
    }

    /**
     * {@inheritdoc}
     */
    public function readByUtilisateur(Utilisateur $utilisateur): array
    {
        return $this->em->getRepository(DoctrineIdee::class)->findBy([
            'utilisateur' => $utilisateur,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Idee $idee)
    {
        $this->em->remove($idee);
        $this->em->flush();
    }
}
