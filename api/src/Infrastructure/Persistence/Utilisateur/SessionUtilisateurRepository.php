<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Utilisateur;

use App\Domain\Utilisateur\Utilisateur;
use App\Domain\Utilisateur\UtilisateurInconnuException;
use App\Domain\Utilisateur\UtilisateurRepository;
use App\Infrastructure\Persistence\SessionRepository;

class SessionUtilisateurRepository extends SessionRepository implements UtilisateurRepository
{
    /**
     * @var Utilisateur[]
     */
    private $repository;

    public function __construct()
    {
        parent::__construct();
        $this->repository = &$this->initSessionRepository('utilisateurs', [
            new Utilisateur(0, 'alice@tkdo.org', 'Alice', 'Alice'),
            new Utilisateur(1, 'bob@tkdo.org', 'Bob', 'Bob'),
            new Utilisateur(2, 'charlie@tkdo.org', 'Charlie', 'Charlie'),
            new Utilisateur(3, 'david@tkdo.org', 'David', 'David'),
        ]);
    }

    // /**
    //  * {@inheritdoc}
    //  */
    // public function findAll(): array
    // {
    //     return array_map(
    //         function (Utilisateur $u) {
    //             return clone $u;
    //         },
    //         array_values($this->repository),
    //     );
    // }

    /**
     * {@inheritdoc}
     */
    public function find(int $id): Utilisateur
    {
        return clone $this->findRaw($id);
    }

    public function findRaw(int $id): Utilisateur
    {
        if (!isset($this->repository[$id])) throw new UtilisateurInconnuException();

        return $this->repository[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function findByIdentifiants(string $identifiant, string $mdp): Utilisateur
    {
        [$utilisateur] = array_filter($this->repository, function ($u) use ($identifiant, $mdp) {
            return ($u->getIdentifiant() === $identifiant) && ($u->getMdp() === $mdp);
        });

        if (!isset($utilisateur)) throw new UtilisateurInconnuException();

        return clone $utilisateur;
    }

    /**
     * {@inheritdoc}
     */
    public function persist(Utilisateur $utilisateur): Utilisateur
    {
        $id = $utilisateur->getId();
        if (!isset($id)) {
            $id = max(array_keys($this->repository)) + 1;
        } elseif (!isset($this->repository[$id])) throw new UtilisateurInconnuException();

        $this->repository[$id] = clone $utilisateur;
        $this->repository[$id]->setId($id);
        return clone $this->repository[$id];
    }
}
