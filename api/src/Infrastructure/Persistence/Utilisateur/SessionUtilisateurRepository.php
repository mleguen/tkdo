<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Utilisateur;

use App\Domain\Utilisateur\Utilisateur;
use App\Domain\Utilisateur\UtilisateurInconnuException;
use App\Domain\Utilisateur\UtilisateurRepository;

class SessionUtilisateurRepository implements UtilisateurRepository
{
    /**
     * InMemoryUtilisateurRepository constructor.
     *
     * @param array|null $utilisateurs
     */
    public function __construct(array $utilisateurs = null)
    {
        if ($utilisateurs || !isset($_SESSION['utilisateurs'])) {
            $_SESSION['utilisateurs'] = $utilisateurs ?? [
                0 => new Utilisateur(0, 'alice@tkdo.org', 'Alice', 'Alice'),
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        return array_values($_SESSION['utilisateurs']);
    }

    /**
     * {@inheritdoc}
     */
    public function findUtilisateurOfId(int $id): Utilisateur
    {
        if (!isset($_SESSION['utilisateurs'][$id])) {
            throw new UtilisateurInconnuException();
        }

        return $_SESSION['utilisateurs'][$id];
    }

    /**
     * {@inheritdoc}
     */
    public function findUtilisateurOfIdentifiants(string $identifiant, string $mdp): ?Utilisateur
    {
        [ $utilisateur ] = array_filter($_SESSION['utilisateurs'], function ($u) use ($identifiant, $mdp) {
            return ($u->getIdentifiant() === $identifiant) && ($u->getMdp() === $mdp);
        });
        
        if (!isset($utilisateur)) throw new UtilisateurInconnuException();

        return $utilisateur;
    }
}
