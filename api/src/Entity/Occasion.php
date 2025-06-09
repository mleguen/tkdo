<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Dto\AddParticipantsInput;
use App\Dto\CreateResultatInput;
use App\Dto\CreateTirageInput;
use App\State\OccasionParticipantProcessor;
use App\State\OccasionResultatProcessor;
use App\State\OccasionTirageProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'occasion')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put(),
        // Custom operations using processors
        new Post(
            uriTemplate: '/occasions/{id}/participants',
            input: AddParticipantsInput::class,
            processor: OccasionParticipantProcessor::class,
            name: 'add_participant'
        ),
        new Post(
            uriTemplate: '/occasions/{id}/resultats',
            input: CreateResultatInput::class,
            processor: OccasionResultatProcessor::class,
            name: 'add_resultat'
        ),
        new Post(
            uriTemplate: '/occasions/{id}/tirage',
            input: CreateTirageInput::class,
            processor: OccasionTirageProcessor::class,
            name: 'generate_tirage'
        )
    ]
)]
class Occasion
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private UuidInterface $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $nom;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $dateEvenement;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $dateLimiteIdee;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $dateLimiteParticipation;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'id_utilisateur', referencedColumnName: 'id', nullable: false)]
    private Utilisateur $utilisateur;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'occasion', targetEntity: Resultat::class)]
    private Collection $resultats;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->resultats = new ArrayCollection();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDateEvenement(): \DateTimeInterface
    {
        return $this->dateEvenement;
    }

    public function setDateEvenement(\DateTimeInterface $dateEvenement): self
    {
        $this->dateEvenement = $dateEvenement;
        return $this;
    }

    public function getDateLimiteIdee(): \DateTimeInterface
    {
        return $this->dateLimiteIdee;
    }

    public function setDateLimiteIdee(\DateTimeInterface $dateLimiteIdee): self
    {
        $this->dateLimiteIdee = $dateLimiteIdee;
        return $this;
    }

    public function getDateLimiteParticipation(): \DateTimeInterface
    {
        return $this->dateLimiteParticipation;
    }

    public function setDateLimiteParticipation(\DateTimeInterface $dateLimiteParticipation): self
    {
        $this->dateLimiteParticipation = $dateLimiteParticipation;
        return $this;
    }

    public function getUtilisateur(): Utilisateur
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(Utilisateur $utilisateur): self
    {
        $this->utilisateur = $utilisateur;
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getResultats(): Collection
    {
        return $this->resultats;
    }
}
