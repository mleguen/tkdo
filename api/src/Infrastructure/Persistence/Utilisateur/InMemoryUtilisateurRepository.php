<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Utilisateur;

use App\Domain\Utilisateur\Utilisateur;
use App\Domain\Utilisateur\UtilisateurNotFoundException;
use App\Domain\Utilisateur\UtilisateurRepository;

class InMemoryUtilisateurRepository implements UtilisateurRepository
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
    public function create(
        string $identifiant,
        string $mdp,
        string $nom,
        string $genre,
        bool $estAdmin
    ): Utilisateur {
        $id = max(array_keys($this->utilisateurs)) + 1;
        $this->utilisateurs[$id] = (new DoctrineUtilisateur($id))
            ->setIdentifiant($identifiant)
            ->setMdp($mdp)
            ->setNom($nom)
            ->setGenre($genre)
            ->setEstAdmin($estAdmin);
        return clone $this->utilisateurs[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $id, bool $reference = false): Utilisateur
    {
        if ($reference) return new InMemoryUtilisateurReference($id);
        return clone $this->readNoClone($id);
    }

    /**
     * {@inheritdoc}
     */
    public function readAll(): array
    {
        return clone $this->utilisateurs;
    }

    public function readNoClone(int $id): Utilisateur
    {
        if (!isset($this->utilisateurs[$id])) throw new UtilisateurNotFoundException();

        return $this->utilisateurs[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function readOneByIdentifiant(string $identifiant): Utilisateur
    {
        $id = array_key_first(array_filter($this->utilisateurs, function ($u) use ($identifiant) {
            return ($u->getIdentifiant() === $identifiant);
        }));

        if (!isset($id)) throw new UtilisateurNotFoundException();

        return clone $this->utilisateurs[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function update(Utilisateur $utilisateur): Utilisateur
    {
        $id = $utilisateur->getId();
        if (!isset($this->utilisateurs[$utilisateur->getId()])) throw new UtilisateurNotFoundException();

        $this->utilisateurs[$id] = clone $utilisateur;
        return clone $this->utilisateurs[$id];
    }
}
