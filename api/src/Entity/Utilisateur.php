<?php

namespace App\Entity;


use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Dto\CreateExclusionInput;
use App\Repository\UtilisateurRepository;
use App\State\UtilisateurExclusionCollectionProvider;
use App\State\UtilisateurExclusionProcessor;
use App\State\UtilisateurReinitMdpProcessor;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\Table(name: 'utilisateur')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put(),
        // Custom operations using processors
        new Post(
            uriTemplate: '/utilisateurs/{id}/reinitmdp',
            processor: UtilisateurReinitMdpProcessor::class,
            name: 'reinit-mdp',
            extraProperties: ['showAsItemlink' => true]
        ),
        new GetCollection(
            uriTemplate: '/utilisateurs/{id}/exclusions',
            provider: UtilisateurExclusionCollectionProvider::class,
            name: 'get-exclusions',
            extraProperties: ['showAsItemlink' => true]
        ),
        new Post(
            uriTemplate: '/utilisateurs/{id}/exclusions',
            input: CreateExclusionInput::class,
            processor: UtilisateurExclusionProcessor::class,
            name: 'create-exclusion',
            extraProperties: ['showAsItemlink' => true]
        )
    ]
)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    private UuidInterface $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $nom;

    #[ORM\Column(type: 'string', length: 255)]
    private string $prenom;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 255)]
    private string $motDePasse;

    #[ORM\Column(type: 'string', enumType: Genre::class)]
    private Genre $genre;

    #[ORM\Column(type: 'string', enumType: PrefNotifIdees::class)]
    private PrefNotifIdees $prefNotifIdees;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // UserInterface implementation
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getPassword(): string
    {
        return $this->motDePasse;
    }

    public function eraseCredentials(): void
    {
        // Nothing to erase
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

    public function getPrenom(): string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getMotDePasse(): string
    {
        return $this->motDePasse;
    }

    public function setMotDePasse(string $motDePasse): self
    {
        $this->motDePasse = $motDePasse;
        return $this;
    }

    public function getGenre(): Genre
    {
        return $this->genre;
    }

    public function setGenre(Genre $genre): self
    {
        $this->genre = $genre;
        return $this;
    }

    public function getPrefNotifIdees(): PrefNotifIdees
    {
        return $this->prefNotifIdees;
    }

    public function setPrefNotifIdees(PrefNotifIdees $prefNotifIdees): self
    {
        $this->prefNotifIdees = $prefNotifIdees;
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
}
