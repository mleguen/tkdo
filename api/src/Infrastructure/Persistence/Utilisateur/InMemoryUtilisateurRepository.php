<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Utilisateur;

use App\Domain\Utilisateur\Utilisateur;
use App\Domain\Utilisateur\UtilisateurNotFoundException;
use App\Domain\Utilisateur\UtilisateurRepository;

class InMemoryUtilisateurRepository implements UtilisateurRepository
{
    /**
     * @var Utilisateur[]
     */
    private $utilisateurs;

    /**
     * InMemoryUtilisateurRepository constructor.
     *
     * @param array|null $utilisateurs
     */
    public function __construct(array $utilisateurs = null)
    {
        $this->utilisateurs = $utilisateurs ?? [
            1 => new Utilisateur(1, 'bill.gates', 'Bill', 'Gates'),
            2 => new Utilisateur(2, 'steve.jobs', 'Steve', 'Jobs'),
            3 => new Utilisateur(3, 'mark.zuckerberg', 'Mark', 'Zuckerberg'),
            4 => new Utilisateur(4, 'evan.spiegel', 'Evan', 'Spiegel'),
            5 => new Utilisateur(5, 'jack.dorsey', 'Jack', 'Dorsey'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return array_values($this->utilisateurs);
    }

    /**
     * {@inheritdoc}
     */
    public function findUtilisateurOfId(int $id): Utilisateur
    {
        if (!isset($this->utilisateurs[$id])) {
            throw new UtilisateurNotFoundException();
        }

        return $this->utilisateurs[$id];
    }
}
