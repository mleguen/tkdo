<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Idee;

use App\Domain\Idee\Idee;
use App\Domain\Idee\IdeeNotFoundException;
use App\Domain\Idee\IdeeRepository;
use App\Domain\Utilisateur\Utilisateur;
use App\Infrastructure\Persistence\Occasion\DoctrineOccasion;
use DateTime;
use Doctrine\ORM\EntityManager;

class DoctrineIdeeRepository implements IdeeRepository
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
        $idee = (new DoctrineIdee())
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
        if ($reference) return $this->em->getReference(DoctrineIdee::class, $id);
        $idee = $this->em->getRepository(DoctrineIdee::class)->find($id);
        if (is_null($idee)) throw new IdeeNotFoundException();
        return $idee;
    }

    /**
     * {@inheritdoc}
     */
    public function readAllByNotifPeriodique(Utilisateur $utilisateur): array
    {
        $classDoctrineIdee = DoctrineIdee::class;
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
    public function readAllByUtilisateur(Utilisateur $utilisateur, bool $supprimee = null): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('i')
            ->from(DoctrineIdee::class, 'i')
            ->where('i.utilisateur = :utilisateur');

        if (!is_null($supprimee)) {
            $qb = $qb->andWhere($supprimee ? $qb->expr()->isNotNull('i.dateSuppression') : $qb->expr()->isNull('i.dateSuppression'));
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
