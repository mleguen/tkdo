<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Resultat;

use App\Domain\Occasion\Occasion;
use App\Domain\Resultat\Resultat;
use App\Domain\Resultat\ResultatRepository;
use App\Domain\Utilisateur\Utilisateur;
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

    public function create(Occasion $occasion, Utilisateur $quiOffre, Utilisateur $quiRecoit): Resultat
    {
        $resultat = (new DoctrineResultat())
            ->setOccasion($occasion)
            ->setQuiOffre($quiOffre)
            ->setQuiRecoit($quiRecoit);
        $this->em->persist($resultat);
        $this->em->flush();
        return $resultat;
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
