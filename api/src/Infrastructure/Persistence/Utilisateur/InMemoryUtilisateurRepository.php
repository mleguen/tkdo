<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Utilisateur;

use App\Domain\Utilisateur\Utilisateur;
use App\Domain\Utilisateur\UtilisateurInconnuException;
use App\Domain\Utilisateur\UtilisateurRepository;
use App\Infrastructure\Persistence\Reference\InMemoryReferenceRepository;

class InMemoryUtilisateurRepository extends InMemoryReferenceRepository implements UtilisateurRepository
{
    /**
     * @var DoctrineUtilisateur[]
     */
    private $utilisateurs;

    /**
     * @param ?DoctrineUtilisateur[] $utilisateurs
     */
    public function __construct(array $utilisateurs = [])
    {
        $this->utilisateurs = $utilisateurs;
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $id): Utilisateur
    {
        if (!isset($this->utilisateurs[$id])) throw new UtilisateurInconnuException();

        return clone $this->utilisateurs[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function readOneByIdentifiants(string $identifiant, string $mdp): Utilisateur
    {
        $id = array_key_first(array_filter($this->utilisateurs, function ($u) use ($identifiant, $mdp) {
            return ($u->getIdentifiant() === $identifiant) && ($u->getMdp() === $mdp);
        }));

        if (!isset($id)) throw new UtilisateurInconnuException();

        return clone $this->utilisateurs[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function update(Utilisateur $utilisateur): Utilisateur
    {
        $id = $utilisateur->getId();
        if (!isset($this->utilisateurs[$utilisateur->getId()])) throw new UtilisateurInconnuException();

        $this->utilisateurs[$id] = clone $utilisateur;
        return clone $this->utilisateurs[$id];
    }
}
