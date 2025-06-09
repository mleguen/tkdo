<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ExclusionRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: ExclusionRepository::class)]
#[ORM\Table(name: 'exclusion')]
#[ApiResource]
class Exclusion
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'id_utilisateur_1', referencedColumnName: 'id', nullable: false)]
    private Utilisateur $utilisateur1;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'id_utilisateur_2', referencedColumnName: 'id', nullable: false)]
    private Utilisateur $utilisateur2;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getUtilisateur1(): Utilisateur
    {
        return $this->utilisateur1;
    }

    public function setUtilisateur1(Utilisateur $utilisateur1): self
    {
        $this->utilisateur1 = $utilisateur1;
        return $this;
    }

    public function getUtilisateur2(): Utilisateur
    {
        return $this->utilisateur2;
    }

    public function setUtilisateur2(Utilisateur $utilisateur2): self
    {
        $this->utilisateur2 = $utilisateur2;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}
