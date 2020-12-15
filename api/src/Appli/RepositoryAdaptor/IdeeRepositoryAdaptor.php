<?php

declare(strict_types=1);

namespace App\Appli\RepositoryAdaptor;

use App\Appli\ModelAdaptor\IdeeAdaptor;
use App\Dom\Exception\IdeeInconnueException;
use App\Dom\Model\Idee;
use App\Dom\Model\Utilisateur;
use App\Dom\Repository\IdeeRepository;
use DateTime;
use Doctrine\ORM\EntityManager;

class IdeeRepositoryAdaptor implements IdeeRepository
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
        Utilisateur $utilisateur,
        string $description,
        Utilisateur $auteur,
        DateTime $dateProposition
    ): Idee
    {
        $idee = (new IdeeAdaptor())
            ->setUtilisateur($utilisateur)
            ->setDescription($description)
            ->setAuteur($auteur)
            ->setDateProposition($dateProposition);
        $this->em->persist($idee);
        $this->em->flush();
        return $idee;
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $id, bool $reference = false): Idee
    {
        if ($reference) return $this->em->getReference(IdeeAdaptor::class, $id);
        $idee = $this->em->getRepository(IdeeAdaptor::class)->find($id);
        if (is_null($idee)) throw new IdeeInconnueException();
        return $idee;
    }

    /**
     * {@inheritdoc}
     */
    public function readAllByNotifPeriodique(Utilisateur $utilisateur): array
    {
        $classDoctrineIdee = IdeeAdaptor::class;
        $dql = <<<EOS
            SELECT DISTINCT i FROM $classDoctrineIdee i
            INNER JOIN i.utilisateur u
            INNER JOIN u.occasions o WITH o.date > CURRENT_TIMESTAMP() AND :utilisateur MEMBER OF o.participants
            WHERE i.utilisateur <> :utilisateur
            AND i.auteur <> :utilisateur
            AND (i.dateProposition >= :dateDerniereNotifPeriodique
                OR (i.dateSuppression IS NOT NULL AND i.dateSuppression >= :dateDerniereNotifPeriodique))
EOS;
        return $this->em->createQuery($dql)
            ->setParameter('utilisateur', $utilisateur)
            ->setParameter('dateDerniereNotifPeriodique', $utilisateur->getDateDerniereNotifPeriodique())
            ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function readAllByUtilisateur(Utilisateur $utilisateur, bool $supprimees = null): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('i')
            ->from(IdeeAdaptor::class, 'i')
            ->where('i.utilisateur = :utilisateur');

        if (!is_null($supprimees)) {
            $qb = $qb->andWhere($supprimees ? $qb->expr()->isNotNull('i.dateSuppression') : $qb->expr()->isNull('i.dateSuppression'));
        }

        return $qb->setParameter('utilisateur', $utilisateur)
            ->getQuery()
            ->getResult();

    }

    /**
     * {@inheritdoc}
     */
    public function update(Idee $idee): Idee
    {
        $this->em->persist($idee);
        $this->em->flush();
        return $idee;
    }
}
