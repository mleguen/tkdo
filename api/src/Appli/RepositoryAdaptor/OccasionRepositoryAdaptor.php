<?php

declare(strict_types=1);

namespace App\Appli\RepositoryAdaptor;

use App\Appli\ModelAdaptor\OccasionAdaptor;
use App\Dom\Exception\OccasionInconnueException;
use App\Dom\Model\Occasion;
use App\Dom\Model\Utilisateur;
use App\Dom\Repository\OccasionRepository;
use DateTime;
use Doctrine\ORM\EntityManager;

class OccasionRepositoryAdaptor implements OccasionRepository
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function create(
        DateTime $date,
        string $titre
    ): Occasion
    {
        $occasion = new OccasionAdaptor()
            ->setDate($date)
            ->setTitre($titre);
        $this->em->persist($occasion);
        $this->em->flush();
        return $occasion;
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $id): Occasion
    {
        /** @var \Doctrine\ORM\EntityRepository */
        $repository = $this->em->getRepository(OccasionAdaptor::class);
        $occasion = $repository->find($id);
        if (is_null($occasion)) throw new OccasionInconnueException();
        return $occasion;
    }

    /**
     * {@inheritdoc}
     */
    public function readAll(): array
    {
        /** @var \Doctrine\ORM\EntityRepository */
        $repository = $this->em->getRepository(OccasionAdaptor::class);
        return $repository->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function readByParticipant(Utilisateur $participant): array
    {
        $occasions = $this->em->createQueryBuilder()
            ->select('o')
            ->from(OccasionAdaptor::class, 'o')
            ->where(':idParticipant MEMBER OF o.participants')
            ->orderBy('o.id', 'ASC')
            ->setParameter('idParticipant', $participant->getId())
            ->getQuery()
            ->getResult();
        return $occasions;
    }

    public function update(Occasion $occasion): Occasion
    {
        $this->em->persist($occasion);
        $this->em->flush();
        return $occasion;
    }
}
