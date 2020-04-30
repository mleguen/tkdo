<?php
declare(strict_types=1);

namespace App\Domain\Utilisateur;

use JsonSerializable;

class Utilisateur implements JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;

   /**
     * @var string
     */
    protected $identifiant;

    /**
     * @var string
     */
    private $mdp;

    /**
     * @var string
     */
    protected $nom;

    /**
     * @param int|null  $id
     * @param string    $identifiant
     * @param string    $mdp
     * @param string    $nom
     */
    public function __construct(?int $id, string $identifiant, string $mdp, string $nom)
    {
        $this->id = $id;
        $this->identifiant = $identifiant;
        $this->mdp = $mdp;
        $this->nom = $nom;
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int    $id
     */
    public function setId(int $id): Utilisateur
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifiant(): string
    {
        return $this->identifiant;
    }

    /**
     * @param string    $identifiant
     */
    public function setIdentifiant(string $identifiant): Utilisateur
    {
        $this->identifiant = $identifiant;
        return $this;
    }

    /**
     * @return string
     */
    public function getMdp(): string
    {
        return $this->mdp;
    }

    /**
     * @param string    $mdp
     */
    public function setMdp(string $mdp): Utilisateur
    {
        $this->mdp = $mdp;
        return $this;
    }

    /**
     * @return string
     */
    public function getNom(): string
    {
        return $this->nom;
    }

    /**
     * @param string    $nom
     */
    public function setNom(string $nom): Utilisateur
    {
        $this->nom = $nom;
        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'identifiant' => $this->identifiant,
            'nom' => $this->nom,
        ];
    }
}
