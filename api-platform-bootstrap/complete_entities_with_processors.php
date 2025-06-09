<?php

// src/Entity/Utilisateur.php (Complete with all operations)
namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Processor\ReinitMdpProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'tkdo_utilisateur')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put(),
        new Delete(),
        new Post(
            uriTemplate: '/utilisateurs/{id}/reinitmdp',
            processor: ReinitMdpProcessor::class,
            name: 'reinit_mdp',
            description: 'Reset user password (admin only)'
        ),
    ],
    normalizationContext: ['groups' => ['utilisateur:read']],
    denormalizationContext: ['groups' => ['utilisateur:write']]
)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['utilisateur:read'])]
    private ?int $id = null;

    #[ORM\Column(unique: true)]
    #[Groups(['utilisateur:read', 'utilisateur:write'])]
    #[Assert\NotBlank]
    private ?string $identifiant = null;

    #[ORM\Column]
    #[Groups(['utilisateur:read', 'utilisateur:write'])]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(['utilisateur:write'])]
    private ?string $mdp = null;

    #[ORM\Column]
    #[Groups(['utilisateur:read', 'utilisateur:write'])]
    #[Assert\NotBlank]
    private ?string $nom = null;

    #[ORM\Column(type: Types::STRING, enumType: Genre::class)]
    #[Groups(['utilisateur:read', 'utilisateur:write'])]
    private ?Genre $genre = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['utilisateur:read', 'utilisateur:write'])]
    private bool $admin = false;

    #[ORM\Column(type: Types::STRING, enumType: PrefNotifIdees::class)]
    #[Groups(['utilisateur:read', 'utilisateur:write'])]
    private PrefNotifIdees $prefNotifIdees = PrefNotifIdees::Aucune;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['utilisateur:read', 'utilisateur:write'])]
    private ?\DateTimeInterface $dateDerniereNotifPeriodique = null;

    #[ORM\ManyToMany(targetEntity: Occasion::class, mappedBy: 'participants')]
    #[Groups(['utilisateur:read'])]
    private Collection $occasions;

    public function __construct()
    {
        $this->occasions = new ArrayCollection();
    }

    // UserInterface implementation
    public function getUserIdentifier(): string
    {
        return $this->identifiant;
    }

    public function getRoles(): array
    {
        return $this->admin ? ['ROLE_ADMIN'] : ['ROLE_USER'];
    }

    public function getPassword(): string
    {
        return $this->mdp;
    }

    public function eraseCredentials(): void
    {
        // Nothing to do here
    }

    // ... all other getters and setters
}

// src/Entity/Idee.php (Complete with all operations)
namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Processor\IdeeSuppressionProcessor;
use App\Provider\IdeeCollectionProvider;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'tkdo_idee')]
#[ApiResource(
    operations: [
        new GetCollection(
            provider: IdeeCollectionProvider::class,
            description: 'Get ideas for a specific user with optional deleted filter'
        ),
        new Post(),
        new Get(),
        new Put(),
        new Delete(),
        new Post(
            uriTemplate: '/idees/{id}/suppression',
            processor: IdeeSuppressionProcessor::class,
            name: 'suppression_idee',
            description: 'Soft delete an idea (author or admin only)'
        ),
    ],
    normalizationContext: ['groups' => ['idee:read']],
    denormalizationContext: ['groups' => ['idee:write']]
)]
class Idee
{
    // ... entity properties and methods as defined earlier
}

// src/Entity/Occasion.php (Complete with all operations)
namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Processor\ParticipantOccasionProcessor;
use App\Processor\ResultatOccasionProcessor;
use App\Processor\TirageOccasionProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'tkdo_occasion')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put(),
        new Delete(),
        new Post(
            uriTemplate: '/occasions/{id}/participant',
            processor: ParticipantOccasionProcessor::class,
            name: 'add_participant',
            description: 'Add a participant to an occasion (admin only)'
        ),
        new Post(
            uriTemplate: '/occasions/{id}/resultat',
            processor: ResultatOccasionProcessor::class,
            name: 'add_resultat',
            description: 'Add a result to an occasion (admin only)'
        ),
        new Post(
            uriTemplate: '/occasions/{id}/tirage',
            processor: TirageOccasionProcessor::class,
            name: 'tirage',
            description: 'Perform the gift draw for an occasion (admin only)'
        ),
    ],
    normalizationContext: ['groups' => ['occasion:read']],
    denormalizationContext: ['groups' => ['occasion:write']]
)]
class Occasion
{
    // ... entity properties and methods as defined earlier
}

// src/Entity/Exclusion.php (Complete with all operations)
namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Processor\ExclusionProcessor;
use App\Provider\ExclusionCollectionProvider;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'tkdo_exclusion')]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/utilisateurs/{idUtilisateur}/exclusions',
            provider: ExclusionCollectionProvider::class,
            description: 'Get exclusions for a specific user (admin only)'
        ),
        new Post(
            uriTemplate: '/utilisateurs/{idUtilisateur}/exclusions',
            processor: ExclusionProcessor::class,
            description: 'Create an exclusion for a user (admin only)'
        ),
        new Get(),
        new Put(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['exclusion:read']],
    denormalizationContext: ['groups' => ['exclusion:write']]
)]
class Exclusion
{
    // ... entity properties and methods as defined earlier
}

// src/Dto/ConnexionInput.php
namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ConnexionInput
{
    #[Assert\NotBlank]
    public string $identifiant;

    #[Assert\NotBlank]
    public string $mdp;
}

// src/Dto/ConnexionOutput.php
namespace App\Dto;

use App\Entity\Utilisateur;

class ConnexionOutput
{
    public string $token;
    public Utilisateur $utilisateur;
}

// src/Resource/Connexion.php
namespace App\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Dto\ConnexionInput;
use App\Dto\ConnexionOutput;
use App\Processor\ConnexionProcessor;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/connexion',
            input: ConnexionInput::class,
            output: ConnexionOutput::class,
            processor: ConnexionProcessor::class,
            name: 'connexion',
            description: 'User authentication endpoint'
        ),
    ],
    shortName: 'Connexion'
)]
class Connexion
{
    // Empty class - just a placeholder for the operation
}
