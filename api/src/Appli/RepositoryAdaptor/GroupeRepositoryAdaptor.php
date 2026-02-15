<?php

declare(strict_types=1);

namespace App\Appli\RepositoryAdaptor;

use App\Appli\ModelAdaptor\GroupeAdaptor;
use App\Dom\Exception\GroupeInconnuException;
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
        if (trim($nom) === '') throw new \InvalidArgumentException('nom cannot be empty');
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
        /** @var \Doctrine\ORM\EntityRepository<GroupeAdaptor> */
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
        /** @var \Doctrine\ORM\EntityRepository<GroupeAdaptor> */
        $repository = $this->em->getRepository(GroupeAdaptor::class);
        return $repository->findAll();
    }

    public function update(Groupe $groupe): Groupe
    {
        $this->em->persist($groupe);
        $this->em->flush();
        return $groupe;
    }
}
