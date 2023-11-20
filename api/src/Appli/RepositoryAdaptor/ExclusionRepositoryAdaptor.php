<?php

declare(strict_types=1);

namespace App\Appli\RepositoryAdaptor;

use App\Appli\ModelAdaptor\ExclusionAdaptor;
use App\Dom\Exception\DoublonExclusionException;
use App\Dom\Model\Exclusion;
use App\Dom\Model\Occasion;
use App\Dom\Model\Utilisateur;
use App\Dom\Repository\ExclusionRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;

class ExclusionRepositoryAdaptor implements ExclusionRepository
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
        Utilisateur $quiOffre,
        Utilisateur $quiNeDoitPasRecevoir
    ): Exclusion
    {
        $exclusion = (new ExclusionAdaptor())
            ->setQuiOffre($quiOffre)
            ->setQuiNeDoitPasRecevoir($quiNeDoitPasRecevoir);
        try {
            $this->em->persist($exclusion);
            $this->em->flush();
        } catch (UniqueConstraintViolationException $err) {
            throw new DoublonExclusionException();
        }
        return $exclusion;
    }

    /**
     * {@inheritdoc}
     */
    public function readByQuiOffre(Utilisateur $quiOffre): array
    {
        $class = ExclusionAdaptor::class;
        $dql = <<<EOS
            SELECT e FROM $class e
            WHERE e.quiOffre = :quiOffre
EOS;
        return $this->em->createQuery($dql)
            ->setParameter('quiOffre', $quiOffre)
            ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function readByParticipantsOccasion(Occasion $occasion): array
    {
        $class = ExclusionAdaptor::class;
        $dql = <<<EOS
            SELECT e FROM $class e
            INNER JOIN e.quiOffre u WITH :occasion MEMBER OF u.occasions
EOS;
        return $this->em->createQuery($dql)
            ->setParameter('occasion', $occasion)
            ->getResult();
    }
}
