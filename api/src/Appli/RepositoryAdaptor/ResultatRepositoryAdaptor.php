<?php

declare(strict_types=1);

namespace App\Appli\RepositoryAdaptor;

use App\Appli\ModelAdaptor\ResultatAdaptor;
use App\Dom\Model\Occasion;
use App\Dom\Model\Resultat;
use App\Dom\Model\Utilisateur;
use App\Dom\Repository\ResultatRepository;
use Doctrine\ORM\EntityManager;

class ResultatRepositoryAdaptor implements ResultatRepository
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
        $resultat = (new ResultatAdaptor())
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
        return $this->em->getRepository(ResultatAdaptor::class)->findBy([
            'occasion' => $occasion,
        ]);
    }
}
