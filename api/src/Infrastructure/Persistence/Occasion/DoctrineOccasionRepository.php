<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Occasion;

use App\Domain\Occasion\Occasion;
use App\Domain\Occasion\OccasionNotFoundException;
use App\Domain\Occasion\OccasionRepository;
use DateTime;
use Doctrine\ORM\EntityManager;

class DoctrineOccasionRepository implements OccasionRepository
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
        $occasion = (new DoctrineOccasion())
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
        $repository = $this->em->getRepository(DoctrineOccasion::class);
        $occasion = $repository->find($id);
        if (is_null($occasion)) throw new OccasionNotFoundException();
        return $occasion;
    }

    /**
     * {@inheritdoc}
     */
    public function readAll(): array
    {
        /** @var \Doctrine\ORM\EntityRepository */
        $repository = $this->em->getRepository(DoctrineOccasion::class);
        return $repository->findAll();
    }

    /**
     * {@inheritdoc}
     */
    public function readByParticipant(int $idParticipant): array
    {
        $occasions = $this->em->createQueryBuilder()
            ->select('o')
            ->from(DoctrineOccasion::class, 'o')
            ->where(':idParticipant MEMBER OF o.participants')
            ->orderBy('o.id', 'ASC')
            ->setParameter('idParticipant', $idParticipant)
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
