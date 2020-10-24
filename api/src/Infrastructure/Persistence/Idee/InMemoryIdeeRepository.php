<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Idee;

use App\Domain\Idee\Idee;
use App\Domain\Idee\IdeeNotFoundException;
use App\Domain\Idee\IdeeRepository;
use App\Domain\Utilisateur\Utilisateur;
use App\Infrastructure\Persistence\Utilisateur\InMemoryUtilisateurRepository;

class InMemoryIdeeRepository implements IdeeRepository
{
    /**
     * @var DoctrineIdee[]
     */
    private $idees;

    /**
     * @var InMemoryUtilisateurRepository
     */
    private $utilisateurRepository;

    /**
     * @param DoctrineIdee[]
     */
    public function __construct(
        array $idees = [],
        InMemoryUtilisateurRepository $utilisateurRepository
    )
    {
        $this->idees = $idees;
        $this->utilisateurRepository = $utilisateurRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function create(
        Utilisateur $utilisateur,
        string $description,
        Utilisateur $auteur,
        \DateTime $dateProposition
    ): Idee {
        $id = max(array_keys($this->idees)) + 1;
        $this->idees[$id] = (new DoctrineIdee($id))
            ->setUtilisateur($this->utilisateurRepository->readNoClone($utilisateur->getId()))
            ->setDescription($description)
            ->setAuteur($this->utilisateurRepository->readNoClone($auteur->getId()))
            ->setDateProposition($dateProposition);
        return clone $this->idees[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $id, bool $reference = false): Idee
    {
        if ($reference) return new DoctrineIdee($id);
        if (!isset($this->idees[$id])) throw new IdeeNotFoundException();
        return clone $this->idees[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function readByUtilisateur(Utilisateur $utilisateur): array
    {
        $idUtilisateur = $utilisateur->getId();
        return array_map(
            function ($i) {
                return clone $i;
            },
            array_values(
                array_filter($this->idees, function ($i) use ($idUtilisateur) {
                    return $i->getUtilisateur()->getId() === $idUtilisateur;
                })
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Idee $idee)
    {
        $id = $idee->getId();
        if (!isset($this->idees[$id])) throw new IdeeNotFoundException();
        unset($this->idees[$id]);
    }
}
