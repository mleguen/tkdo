<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Idee;

use App\Domain\Idee\Idee;
use App\Domain\Idee\IdeeInconnueException;
use App\Domain\Idee\IdeeRepository;
use App\Domain\Reference\Reference;
use App\Domain\Utilisateur\Utilisateur;
use App\Infrastructure\Persistence\Reference\InMemoryReferenceRepository;

class InMemoryIdeeRepository extends InMemoryReferenceRepository implements IdeeRepository
{
    /**
     * @var DoctrineIdee[]
     */
    private $idees;

    /**
     * @param DoctrineIdee[]
     */
    public function __construct(array $idees = [])
    {
        $this->idees = $idees;
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
            ->setUtilisateur($utilisateur)
            ->setDescription($description)
            ->setAuteur($auteur)
            ->setDateProposition($dateProposition);
        return clone $this->idees[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $id): Idee
    {
        if (!isset($this->idees[$id])) throw new IdeeInconnueException();
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
    public function delete(Reference $idee)
    {
        $id = $idee->getId();
        if (!isset($this->idees[$id])) throw new IdeeInconnueException();
        unset($this->idees[$id]);
    }
}
