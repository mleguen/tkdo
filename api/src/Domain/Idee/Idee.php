<?php

declare(strict_types=1);

namespace App\Domain\Idee;

use App\Domain\Utilisateur\Utilisateur;
use JsonSerializable;

class Idee implements JsonSerializable
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var Utilisateur
     */
    private $utilisateur;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var Utilisateur
     */
    protected $auteur;

    /**
     * @var \DateTime
     */
    protected $dateProposition;

    /**
     * @param int|null      $id
     * @param Utilisateur   $utilisateur
     * @param string        $description
     * @param Utilisateur   $auteur
     * @param \DateTime     $dateProposition
     */
    public function __construct(
        ?int $id,
        Utilisateur $utilisateur,
        string $description,
        Utilisateur $auteur,
        ?\DateTime $dateProposition = null
    ) {
        $this->id = $id;
        $this->utilisateur = $utilisateur;
        $this->description = $description;
        $this->auteur = $auteur;
        $this->dateProposition = $dateProposition ?? new \DateTime();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Idee
     */
    public function setId(int $id): Idee
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return Utilisateur
     */
    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    /**
     * @param Utilisateur $utilisateur
     * @return Idee
     */
    public function setUtilisateur(Utilisateur $utilisateur): Idee
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return Utilisateur
     */
    public function getAuteur(): Utilisateur
    {
        return $this->auteur;
    }

    /**
     * @param Utilisateur $auteur
     * @return Idee
     */
    public function setAuteur(Utilisateur $auteur): Idee
    {
        $this->auteur = $auteur;
        return $this;
    }

    /**
     * @return string
     */
    public function getDateProposition(): \DateTime
    {
        return $this->dateProposition;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'auteur' => $this->auteur,
            'dateProposition' => $this->dateProposition->format(\DateTimeInterface::ISO8601),
        ];
    }

    public function __clone() {
        $this->utilisateur = clone $this->utilisateur;
        $this->auteur = clone $this->auteur;
        $this->dateProposition = clone $this->dateProposition;
    }
}
