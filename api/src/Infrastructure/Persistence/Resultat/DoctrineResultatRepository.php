<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Resultat;

use App\Domain\Occasion\Occasion;
use App\Domain\Resultat\ResultatRepository;
use App\Infrastructure\Persistence\Occasion\DoctrineOccasion;
use Doctrine\ORM\EntityManager;

class DoctrineResultatRepository implements ResultatRepository
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
    public function readByOccasion(Occasion $occasion): array
    {
        return $this->em->getRepository(DoctrineResultat::class)->findBy([
            'occasion' => $occasion,
        ]);
    }
}
