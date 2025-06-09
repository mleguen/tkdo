<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'resultat')]
#[ApiResource]
class Resultat
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private UuidInterface $id;

    #[ORM\ManyToOne(targetEntity: Occasion::class, inversedBy: 'resultats')]
    #[ORM\JoinColumn(name: 'id_occasion', referencedColumnName: 'id', nullable: false)]
    private Occasion $occasion;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'id_donneur', referencedColumnName: 'id', nullable: false)]
    private Utilisateur $donneur;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'id_receveur', referencedColumnName: 'id', nullable: false)]
    private Utilisateur $receveur;

    #[ORM\ManyToOne(targetEntity: Idee::class)]
    #[ORM\JoinColumn(name: 'id_idee', referencedColumnName: 'id', nullable: true)]
    private ?Idee $idee = null;

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

    public function getOccasion(): Occasion
    {
        return $this->occasion;
    }

    public function setOccasion(Occasion $occasion): self
    {
        $this->occasion = $occasion;
        return $this;
    }

    public function getDonneur(): Utilisateur
    {
        return $this->donneur;
    }

    public function setDonneur(Utilisateur $donneur): self
    {
        $this->donneur = $donneur;
        return $this;
    }

    public function getReceveur(): Utilisateur
    {
        return $this->receveur;
    }

    public function setReceveur(Utilisateur $receveur): self
    {
        $this->receveur = $receveur;
        return $this;
    }

    public function getIdee(): ?Idee
    {
        return $this->idee;
    }

    public function setIdee(?Idee $idee): self
    {
        $this->idee = $idee;
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
