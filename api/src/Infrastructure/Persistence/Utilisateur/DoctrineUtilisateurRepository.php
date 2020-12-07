<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Utilisateur;

use App\Domain\Utilisateur\PrefNotifIdees;
use App\Domain\Utilisateur\Utilisateur;
use App\Domain\Utilisateur\UtilisateurNotFoundException;
use App\Domain\Utilisateur\UtilisateurRepository;
use App\Infrastructure\Persistence\Occasion\DoctrineOccasion;
use Doctrine\ORM\EntityManager;

class DoctrineUtilisateurRepository implements UtilisateurRepository
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
        string $mdp,
        string $nom,
        string $genre,
        bool $estAdmin,
        string $prefNotifIdees
    ): Utilisateur
    {
        $utilisateur = (new DoctrineUtilisateur())
            ->setIdentifiant($identifiant)
            ->setEmail($email)
            ->setMdp($mdp)
            ->setNom($nom)
            ->setGenre($genre)
            ->setEstAdmin($estAdmin)
            ->setPrefNotifIdees($prefNotifIdees);
        $this->em->persist($utilisateur);
        $this->em->flush();
        return $utilisateur;
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $id, bool $reference = false): Utilisateur
    {
        if ($reference) return $this->em->getReference(DoctrineUtilisateur::class, $id);
        $utilisateur = $this->em->getRepository(DoctrineUtilisateur::class)->find($id);
        if (is_null($utilisateur)) throw new UtilisateurNotFoundException();
        return $utilisateur;
    }

    /**
     * {@inheritdoc}
     */
    public function readAll(): array
    {
        $utilisateurs = $this->em->getRepository(DoctrineUtilisateur::class)->findAll();
        return $utilisateurs;
    }

    /**
     * {@inheritdoc}
     */
    public function readAllByNotifInstantaneePourIdees(int $idUtilisateur, int $idActeur): array
    {
        return $this->em->createQueryBuilder()
            ->select('u')
            ->distinct()
            ->from(DoctrineUtilisateur::class, 'u')
            ->innerJoin(DoctrineOccasion::class, 'o', 'u.id MEMBER OF o.participants')
            ->where('u.prefNotifIdees = :prefNotifIdees')
            ->andWhere('u.id <> :idUtilisateur')
            ->andWhere('u.id <> :idActeur')
            ->andWhere('o.date > CURRENT_TIMESTAMP()')
            ->andWhere(':idUtilisateur MEMBER OF o.participants')
            ->setParameter('prefNotifIdees', PrefNotifIdees::Instantanee)
            ->setParameter('idUtilisateur', $idUtilisateur)
            ->setParameter('idActeur', $idActeur)
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function readOneByIdentifiant(string $identifiant): Utilisateur
    {        
        /**
         * @var DoctrineUtilisateur
         */
        $utilisateur = $this->em->getRepository(DoctrineUtilisateur::class)->findOneBy([
            'identifiant' => $identifiant
        ]);
        if (is_null($utilisateur)) {
            throw new UtilisateurNotFoundException();
        }
        return $utilisateur;
    }

    /**
     * {@inheritdoc}
     */
    public function update(Utilisateur $utilisateur): Utilisateur
    {
        $this->em->persist($utilisateur);
        $this->em->flush();
        return $utilisateur;
    }
}
