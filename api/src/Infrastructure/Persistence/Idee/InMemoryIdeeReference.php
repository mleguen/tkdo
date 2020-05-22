<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Idee;

use App\Domain\Idee\Idee;
use App\Domain\ReferenceException;
use App\Domain\Utilisateur\Utilisateur;

class InMemoryIdeeReference implements Idee
{
    /**
     * @var int
     */
    protected $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        throw new ReferenceException();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuteur(): Utilisateur
    {
        throw new ReferenceException();
    }

    /**
     * {@inheritdoc}
     */
    public function getDateProposition(): \DateTime
    {
        throw new ReferenceException();
    }

    /**
     * {@inheritdoc}
     */
    public function setUtilisateur(Utilisateur $utilisateur): Idee
    {
        throw new ReferenceException();
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription(string $description): Idee
    {
        throw new ReferenceException();
    }
    
    /**
     * {@inheritdoc}
     */
    public function setAuteur(Utilisateur $auteur): Idee
    {
        throw new ReferenceException();
    }
    
    /**
     * {@inheritdoc}
     */
    public function setDateProposition(\DateTime $dateProposition): Idee
    {
        throw new ReferenceException();
    }
}
