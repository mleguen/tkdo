<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Idee;

use App\Domain\Idee\Idee;
use App\Domain\Utilisateur\Utilisateur;
use App\Infrastructure\Persistence\Reference\InMemoryReference;

class InMemoryIdee extends InMemoryReference implements Idee
{
    /**
     * @var Utilisateur
     */
    private $utilisateur;

    /**
     * @var string
     */
    private $description;

    /**
     * @var Utilisateur
     */
    private $auteur;

    /**
     * @var \DateTime
     */
    private $dateProposition;

    public function __construct(
        int $id,
        Utilisateur $utilisateur,
        string $description,
        Utilisateur $auteur,
        \DateTime $dateProposition
    ) {
        parent::__construct($id);
        $this->utilisateur = $utilisateur;
        $this->description = $description;
        $this->auteur = $auteur;
        $this->dateProposition = $dateProposition;
    }

    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuteur(): Utilisateur
    {
        return $this->auteur;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateProposition(): \DateTime
    {
        return $this->dateProposition;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone() {
        $this->utilisateur = clone $this->utilisateur;
        $this->auteur = clone $this->auteur;
        $this->dateProposition = clone $this->dateProposition;
    }
}
