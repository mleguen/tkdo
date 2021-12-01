<?php

declare(strict_types=1);

namespace App\Appli\RepositoryAdaptor;

use App\Appli\ModelAdaptor\ExclusionAdaptor;
use App\Dom\Model\Occasion;
use App\Dom\Repository\ExclusionRepository;
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
