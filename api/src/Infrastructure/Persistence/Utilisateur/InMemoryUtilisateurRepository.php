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
     * @var InMemoryUtilisateur[]
     */
    private $utilisateurs;

    /**
     * @param ?InMemoryUtilisateur[] $utilisateurs
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
        $utilisateurs = array_filter($this->utilisateurs, function ($u) use ($identifiant, $mdp) {
            return ($u->getIdentifiant() === $identifiant) && ($u->getMdp() === $mdp);
        });

        if (!isset($utilisateurs[0])) throw new UtilisateurInconnuException();

        return clone $utilisateurs[0];
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
