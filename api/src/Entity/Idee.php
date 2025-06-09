<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\IdeeRepository;
use App\State\IdeeCollectionProvider;
use App\State\IdeeSuppressionProcessor;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: IdeeRepository::class)]
#[ORM\Table(name: 'idee')]
#[ApiResource(
    operations: [
        new GetCollection(provider: IdeeCollectionProvider::class),
        new Post(),
        new Get(),
        new Put(),
        // Custom soft delete operation
        new Post(
            uriTemplate: '/idees/{id}/suppression',
            processor: IdeeSuppressionProcessor::class,
            name: 'soft_delete'
        )
    ]
)]
class Idee
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private UuidInterface $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $titre;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $url = null;

    #[ORM\ManyToOne(targetEntity: Utilisateur::class)]
    #[ORM\JoinColumn(name: 'id_utilisateur', referencedColumnName: 'id', nullable: false)]
    private Utilisateur $utilisateur;

    #[ORM\Column(type: 'boolean')]
    private bool $supprimee = false;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getTitre(): string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
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

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;
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

    public function isSupprimee(): bool
    {
        return $this->supprimee;
    }

    public function setSupprimee(bool $supprimee): self
    {
        $this->supprimee = $supprimee;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
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

    public function getAuteur(): Utilisateur
    {
        return $this->utilisateur;
    }

    public function setAuteur(Utilisateur $auteur): self
    {
        $this->utilisateur = $auteur;
        return $this;
    }

    public function getDateSuppression(): ?\DateTimeImmutable
    {
        return $this->updatedAt; // Assuming updatedAt is used for soft delete timestamp
    }

    public function setDateSuppression(?\DateTimeImmutable $dateSuppression): self
    {
        $this->updatedAt = $dateSuppression;
        return $this;
    }
}
