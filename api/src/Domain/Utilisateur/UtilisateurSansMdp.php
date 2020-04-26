<?php
declare(strict_types=1);

namespace App\Domain\Utilisateur;

use JsonSerializable;

class UtilisateurSansMdp implements JsonSerializable
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
    protected $nom;

    /**
     * @param int|null  $id
     * @param string    $identifiant
     * @param string    $identifiant
     * @param string    $nom
     */
    public function __construct(?int $id, string $identifiant, string $nom)
    {
        $this->id = $id;
        $this->identifiant = $identifiant;
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
     * @return string
     */
    public function getIdentifiant(): string
    {
        return $this->identifiant;
    }

    /**
     * @param string    $identifiant
     */
    public function setIdentifiant(string $identifiant)
    {
        $this->identifiant = $identifiant;
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
    public function setNom(string $nom)
    {
        $this->nom = $nom;
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
