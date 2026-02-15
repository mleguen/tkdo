<?php

declare(strict_types=1);

namespace App\Appli\RepositoryAdaptor;

use App\Appli\ModelAdaptor\OccasionAdaptor;
use App\Appli\ModelAdaptor\UtilisateurAdaptor;
use App\Dom\Exception\IdentifiantDejaUtiliseException;
use App\Dom\Exception\UtilisateurInconnuException;
use App\Dom\Model\PrefNotifIdees;
use App\Dom\Model\Utilisateur;
use App\Dom\Repository\UtilisateurRepository;
use DateTime;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;

class UtilisateurRepositoryAdaptor implements UtilisateurRepository
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
        string $identifiant,
        string $email,
        string $mdpClair,
        string $nom,
        string $genre,
        bool $admin,
        string $prefNotifIdees,
        DateTime $dateDerniereNotifPeriodique
    ): Utilisateur
    {
        $utilisateur = new UtilisateurAdaptor()
            ->setIdentifiant($identifiant)
            ->setEmail($email)
            ->setMdpClair($mdpClair)
            ->setNom($nom)
            ->setGenre($genre)
            ->setAdmin($admin)
            ->setPrefNotifIdees($prefNotifIdees)
            ->setDateDerniereNotifPeriodique($dateDerniereNotifPeriodique);
        try {
            $this->em->persist($utilisateur);
            $this->em->flush();
        } catch (UniqueConstraintViolationException) {
            throw new IdentifiantDejaUtiliseException();
        }
        return $utilisateur;
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $id, bool $reference = false): Utilisateur
    {
        if ($reference) {
            /** @var Utilisateur */
            return $this->em->getReference(UtilisateurAdaptor::class, $id);
        }
        /** @var Utilisateur|null */
        $utilisateur = $this->em->getRepository(UtilisateurAdaptor::class)->find($id);
        if (is_null($utilisateur)) throw new UtilisateurInconnuException();
        return $utilisateur;
    }

    /**
     * {@inheritdoc}
     */
    public function readAll(): array
    {
        $utilisateurs = $this->em->getRepository(UtilisateurAdaptor::class)->findAll();
        return $utilisateurs;
    }

    /**
     * {@inheritdoc}
     */
    public function readAllByNotifInstantaneePourIdees(Utilisateur $utilisateur): array
    {
        $classDoctrineUtilisateur = UtilisateurAdaptor::class;
        $dql = <<<EOS
            SELECT DISTINCT u FROM $classDoctrineUtilisateur u
            INNER JOIN u.occasions o WITH u MEMBER OF o.participants
            WHERE u <> :utilisateur
            AND u.prefNotifIdees = :prefNotifIdees
            AND o.date > CURRENT_TIMESTAMP()
            AND :utilisateur MEMBER OF o.participants
EOS;
        return $this->em->createQuery($dql)
            ->setParameter('utilisateur', $utilisateur)
            ->setParameter('prefNotifIdees', PrefNotifIdees::Instantanee)
            ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function readAllByNotifPeriodique(string $prefNotifIdees, DateTime $dateDebutPeriode): array
    {
        $classDoctrineUtilisateur = UtilisateurAdaptor::class;
        $dql = <<<EOS
            SELECT u
            FROM $classDoctrineUtilisateur u
            WHERE u.prefNotifIdees = :prefNotifIdees
            AND u.dateDerniereNotifPeriodique < :dateDebutPeriode
EOS;
        return $this->em->createQuery($dql)
            ->setParameter('prefNotifIdees', $prefNotifIdees)
            ->setParameter('dateDebutPeriode', $dateDebutPeriode)
            ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function readOneByIdentifiant(string $identifiant): Utilisateur
    {
        /** @var Utilisateur|null */
        $utilisateur = $this->em->getRepository(UtilisateurAdaptor::class)->findOneBy([
            'identifiant' => $identifiant
        ]);
        if (is_null($utilisateur)) {
            throw new UtilisateurInconnuException();
        }
        return $utilisateur;
    }

    /**
     * {@inheritdoc}
     */
    public function readOneByIdentifiantOuEmail(string $identifiantOuEmail): Utilisateur
    {
        $classDoctrineUtilisateur = UtilisateurAdaptor::class;
        $dql = <<<EOS
            SELECT u FROM $classDoctrineUtilisateur u
            WHERE u.identifiant = :val OR u.email = :val
EOS;
        /** @var Utilisateur|null */
        $result = $this->em->createQuery($dql)
            ->setParameter('val', $identifiantOuEmail)
            ->getOneOrNullResult();
        if ($result === null) {
            throw new UtilisateurInconnuException();
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function update(Utilisateur $utilisateur): Utilisateur
    {
        try {
            $this->em->persist($utilisateur);
            $this->em->flush();
        } catch (UniqueConstraintViolationException) {
            throw new IdentifiantDejaUtiliseException();
        }
        return $utilisateur;
    }
}
