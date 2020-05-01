<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Idee;

use App\Domain\Idee\Idee;
use App\Domain\Idee\IdeeInconnueException;
use App\Domain\Idee\IdeeRepository;
use App\Domain\Utilisateur\Utilisateur;
use App\Infrastructure\Persistence\SessionRepository;
use App\Infrastructure\Persistence\Utilisateur\SessionUtilisateurRepository;

class SessionIdeeRepository extends SessionRepository implements IdeeRepository
{
    /**
     * @var Idee[]
     */
    private $repository;

    /**
     * @var SessionUtilisateurRepository
     */
    private $utilisateurRepository;

    public function __construct(SessionUtilisateurRepository $utilisateurRepository)
    {
        parent::__construct();
        $this->utilisateurRepository = $utilisateurRepository;
        $this->repository = &$this->initSessionRepository('idees', [
            new Idee(
                0,
                $this->utilisateurRepository->find(0),
                "un gauffrier",
                $this->utilisateurRepository->find(0),
                \DateTime::createFromFormat('d/m/Y', '19/04/2020')
            ),
            new Idee(
                1,
                $this->utilisateurRepository->find(1),
                "une canne à pêche",
                $this->utilisateurRepository->find(0),
                \DateTime::createFromFormat('d/m/Y', '19/04/2020')
            ),
            new Idee(
                2,
                $this->utilisateurRepository->find(1),
                "des gants de boxe",
                $this->utilisateurRepository->find(1),
                \DateTime::createFromFormat('d/m/Y', '07/04/2020')
            ),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllByUtilisateur(Utilisateur $utilisateur): array
    {
        $idUtilisateur = $utilisateur->getId();
        return array_map(
            function($i) {
                return clone $i;
            },
            array_values(
                array_filter($this->repository, function ($i) use ($idUtilisateur) {
                    return $i->getUtilisateur()->getId() === $idUtilisateur;
                })
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function find(int $id): Idee
    {
        if (!isset($this->repository[$id])) {
            throw new IdeeInconnueException();
        }

        return clone $this->repository[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function persist(Idee $idee): Idee
    {
        $id = $idee->getId();
        if (!isset($id)) {
            $id = max(array_keys($this->repository)) + 1;
        } elseif (!isset($this->repository[$id])) throw new IdeeInconnueException();

        $this->repository[$id] = clone $idee;
        $this->repository[$id]
            ->setId($id)
            ->setUtilisateur($this->utilisateurRepository->findRaw($idee->getUtilisateur()->getId()))
            ->setAuteur($this->utilisateurRepository->findRaw($idee->getAuteur()->getId()));
        return clone $this->repository[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function remove(Idee $idee)
    {
        $id = $idee->getId();
        if (!isset($this->repository[$id])) throw new IdeeInconnueException();
        unset($this->repository[$id]);
    }
}
