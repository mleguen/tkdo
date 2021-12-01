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

    public function deleteByOccasion(Occasion $occasion)
    {
        $class = ResultatAdaptor::class;
        $dql = <<<EOS
            DELETE FROM $class r
            WHERE r.occasion = :occasion
EOS;
        return $this->em->createQuery($dql)
            ->setParameter('occasion', $occasion)
            ->getResult();
    }

    public function hasForOccasion(Occasion $occasion): bool
    {
        $class = ResultatAdaptor::class;
        $dql = <<<EOS
            SELECT COUNT(r.quiOffre) FROM $class r
            WHERE r.occasion = :occasion
EOS;
        return $this->em->createQuery($dql)
            ->setParameter('occasion', $occasion)
            ->getSingleScalarResult() > 0;
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

    public function readByParticipantsOccasion(Occasion $occasion): array
    {
        $class = ResultatAdaptor::class;
        $dql = <<<EOS
            SELECT r FROM $class r
            INNER JOIN r.quiOffre u WITH :occasion MEMBER OF u.occasions
EOS;
        return $this->em->createQuery($dql)
            ->setParameter('occasion', $occasion)
            ->getResult();
    }
}
