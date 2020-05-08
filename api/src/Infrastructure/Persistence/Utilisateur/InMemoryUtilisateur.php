<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Utilisateur;

use App\Domain\Utilisateur\Utilisateur;
use App\Infrastructure\Persistence\Reference\InMemoryReference;

class InMemoryUtilisateur extends InMemoryReference implements Utilisateur
{
   /**
     * @var string
     */
    private $identifiant;

    /**
     * @var string
     */
    private $mdp;

    /**
     * @var string
     */
    private $nom;

    public function __construct(int $id, string $identifiant, string $mdp, string $nom)
    {
        parent::__construct($id);
        $this->identifiant = $identifiant;
        $this->mdp = $mdp;
        $this->nom = $nom;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifiant(): string
    {
        return $this->identifiant;
    }

    public function getMdp(): string
    {
        return $this->mdp;
    }

    /**
     * {@inheritdoc}
     */
    public function getNom(): string
    {
        return $this->nom;
    }

    /**
     * {@inheritdoc}
     */
    public function setIdentifiant(string $identifiant): Utilisateur
    {
        $this->identifiant = $identifiant;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMdp(string $mdp): Utilisateur
    {
        $this->mdp = $mdp;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setNom(string $nom): Utilisateur
    {
        $this->nom = $nom;
        return $this;
    }
}
