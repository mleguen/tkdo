<?php

declare(strict_types=1);

namespace App\Appli\RepositoryAdaptor;

use App\Appli\ModelAdaptor\AppartenanceAdaptor;
use App\Appli\ModelAdaptor\GroupeAdaptor;
use App\Dom\Exception\GroupeInconnuException;
use App\Dom\Model\Appartenance;
use App\Dom\Model\Groupe;
use App\Dom\Repository\GroupeRepository;
use DateTime;
use Doctrine\ORM\EntityManager;

class GroupeRepositoryAdaptor implements GroupeRepository
{
    protected EntityManager $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function create(string $nom): Groupe
    {
        if (trim($nom) === '') throw new \InvalidArgumentException('le nom ne peut pas être vide');
        $groupe = new GroupeAdaptor();
        $groupe->setNom($nom)
            ->setDateCreation(new DateTime());
        $this->em->persist($groupe);
        $this->em->flush();
        return $groupe;
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $id): Groupe
    {
        $repository = $this->em->getRepository(GroupeAdaptor::class);
        /** @var Groupe|null */
        $groupe = $repository->find($id);
        if (is_null($groupe)) throw new GroupeInconnuException();
        return $groupe;
    }

    /**
     * {@inheritdoc}
     */
    public function readAll(): array
    {
        $repository = $this->em->getRepository(GroupeAdaptor::class);
        return $repository->findAll();
    }

    /**
     * @return Appartenance[]
     */
    #[\Override]
    public function readAppartenancesForUtilisateur(int $utilisateurId): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('a')
            // Eager-load Groupe entities to prevent N+1 queries when accessing getGroupe()->getId()
            ->addSelect('g')
            ->from(AppartenanceAdaptor::class, 'a')
            ->join('a.groupe', 'g')
            ->where('a.utilisateur = :utilisateurId')
            ->andWhere('g.archive = false')
            ->setParameter('utilisateurId', $utilisateurId);

        /** @var Appartenance[] */
        return $qb->getQuery()->getResult();
    }

    /**
     * @return Appartenance[]
     */
    #[\Override]
    public function readToutesAppartenancesForUtilisateur(int $utilisateurId): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('a')
            ->addSelect('g')
            ->from(AppartenanceAdaptor::class, 'a')
            ->join('a.groupe', 'g')
            ->where('a.utilisateur = :utilisateurId')
            ->setParameter('utilisateurId', $utilisateurId);

        /** @var Appartenance[] */
        return $qb->getQuery()->getResult();
    }

    public function update(Groupe $groupe): Groupe
    {
        if (trim($groupe->getNom()) === '') throw new \InvalidArgumentException('le nom ne peut pas être vide');
        $this->em->persist($groupe);
        $this->em->flush();
        return $groupe;
    }
}
