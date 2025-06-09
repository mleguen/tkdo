# API Platform Bootstrap Instructions - Using Processors for Custom Operations

## Overview
This guide will help you migrate your existing Slim framework application to API Platform, using **processors** instead of custom controllers for all custom operations. We'll maintain the same entity structure and functionality while leveraging API Platform's modern architecture.

## Quick Start (Recommended)

The fastest way to get started with a modern API Platform project compatible with PHP 8.4:

```bash
# Option 1: Use Symfony CLI (if installed)
symfony new my-api-platform-project --version="7.0.*" --webapp
cd my-api-platform-project
composer require api-platform/api-platform

# Option 2: Use Composer directly
composer create-project symfony/skeleton:"^7.0" my-api-platform-project
cd my-api-platform-project
composer require api-platform/api-platform doctrine/orm doctrine/doctrine-migrations-bundle

# Configure database
echo "DATABASE_URL=sqlite:///%kernel.project_dir%/var/data.db" >> .env.local

# Generate JWT keys for authentication
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout

# Add JWT configuration to .env.local
echo "JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem" >> .env.local
echo "JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem" >> .env.local
echo "JWT_PASSPHRASE=your_passphrase_here" >> .env.local
```

## Step 1: Project Setup

### 1.1 Create New Symfony Project with API Platform
```bash
# Create a new Symfony 7.x project (compatible with PHP 8.4)
composer create-project symfony/skeleton:"^7.0" my-api-platform-project
cd my-api-platform-project

# Install API Platform
composer require api-platform/api-platform

# Install additional dependencies
composer require symfony/security-bundle
composer require doctrine/doctrine-migrations-bundle
composer require symfony/mailer
composer require ramsey/uuid-doctrine
composer require lexik/jwt-authentication-bundle
composer require firebase/php-jwt
```

### 1.2 Alternative: Use API Platform Docker Distribution (Recommended)
```bash
# Clone the API Platform distribution (modern approach)
git clone https://github.com/api-platform/api-platform.git my-api-platform-project
cd my-api-platform-project

# Or download the latest distribution
curl -LO https://github.com/api-platform/api-platform/archive/refs/heads/main.zip
unzip main.zip
mv api-platform-main my-api-platform-project
cd my-api-platform-project

# Install dependencies
composer install
```

### 1.3 Manual Setup for Existing Symfony Project
If you prefer to start with a clean Symfony project:
```bash
# Create Symfony 7.x project
composer create-project symfony/skeleton:"^7.0" my-api-platform-project
cd my-api-platform-project

# Install API Platform and required packages
composer require api-platform/api-platform
composer require doctrine/orm
composer require doctrine/doctrine-bundle
composer require doctrine/doctrine-migrations-bundle
composer require symfony/security-bundle
composer require symfony/mailer
composer require ramsey/uuid-doctrine
composer require lexik/jwt-authentication-bundle
composer require firebase/php-jwt
composer require nelmio/cors-bundle
```

## Step 2: Entity Migration

### 2.1 Create Base Entities Directory
```bash
mkdir -p src/Entity
```

### 2.2 Migrate Entities with API Platform Attributes

Create the following entities with proper API Platform annotations and custom operations:

#### 2.2.1 Genre Enum
```php
<?php
// src/Entity/Genre.php
namespace App\Entity;

enum Genre: string
{
    case HOMME = 'H';
    case FEMME = 'F';
}
```

#### 2.2.2 PrefNotifIdees Enum
```php
<?php
// src/Entity/PrefNotifIdees.php
namespace App\Entity;

enum PrefNotifIdees: string
{
    case JAMAIS = 'jamais';
    case QUOTIDIENNE = 'quotidienne';
    case HEBDOMADAIRE = 'hebdomadaire';
}
```

#### 2.2.3 Utilisateur Entity with Custom Operations
```php
<?php
// src/Entity/Utilisateur.php
namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Processor\ReinitMdpProcessor;
use App\Provider\ExclusionCollectionProvider;
use App\Processor\ExclusionProcessor;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
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
            processor: ReinitMdpProcessor::class,
            name: 'reinit_mdp'
        ),
        new GetCollection(
            uriTemplate: '/utilisateurs/{id}/exclusions',
            provider: ExclusionCollectionProvider::class,
            name: 'get_exclusions'
        ),
        new Post(
            uriTemplate: '/utilisateurs/{id}/exclusions',
            processor: ExclusionProcessor::class,
            name: 'create_exclusion'
        )
    ]
)]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private string $id;

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

    // Constructor, getters, setters...
    public function __construct()
    {
        $this->id = \Ramsey\Uuid\Uuid::uuid4()->toString();
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

    // Getters and setters...
}
```

#### 2.2.4 Idee Entity with Custom Operations
```php
<?php
// src/Entity/Idee.php
namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Provider\IdeeCollectionProvider;
use App\Processor\IdeeSuppressionProcessor;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
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
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private string $id;

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
        $this->id = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $this->createdAt = new \DateTimeImmutable();
    }

    // Getters and setters...
}
```

#### 2.2.5 Occasion Entity with Custom Operations
```php
<?php
// src/Entity/Occasion.php
namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Processor\ParticipantOccasionProcessor;
use App\Processor\ResultatOccasionProcessor;
use App\Processor\TirageOccasionProcessor;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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
            processor: ParticipantOccasionProcessor::class,
            name: 'add_participant'
        ),
        new Post(
            uriTemplate: '/occasions/{id}/resultats',
            processor: ResultatOccasionProcessor::class,
            name: 'add_resultat'
        ),
        new Post(
            uriTemplate: '/occasions/{id}/tirage',
            processor: TirageOccasionProcessor::class,
            name: 'generate_tirage'
        )
    ]
)]
class Occasion
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private string $id;

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
        $this->id = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $this->createdAt = new \DateTimeImmutable();
        $this->resultats = new ArrayCollection();
    }

    // Getters and setters...
}
```

#### 2.2.6 Exclusion Entity
```php
<?php
// src/Entity/Exclusion.php
namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'exclusion')]
#[ApiResource]
class Exclusion
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private string $id;

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
        $this->id = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $this->createdAt = new \DateTimeImmutable();
    }

    // Getters and setters...
}
```

#### 2.2.7 Resultat Entity
```php
<?php
// src/Entity/Resultat.php
namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'resultat')]
#[ApiResource]
class Resultat
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private string $id;

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
        $this->id = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $this->createdAt = new \DateTimeImmutable();
    }

    // Getters and setters...
}
```

## Step 3: Create Custom Processors

### 3.1 Authentication Processor (Connexion)

#### 3.1.1 Create DTOs
```php
<?php
// src/Dto/ConnexionInput.php
namespace App\Dto;

class ConnexionInput
{
    public string $email;
    public string $motDePasse;
}
```

```php
<?php
// src/Dto/ConnexionOutput.php
namespace App\Dto;

class ConnexionOutput
{
    public function __construct(
        public string $token,
        public string $utilisateurId,
        public string $nom,
        public string $prenom
    ) {}
}
```

#### 3.1.2 Create Connexion Resource
```php
<?php
// src/Entity/Connexion.php
namespace App\Entity;

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
            name: 'connexion'
        )
    ]
)]
class Connexion
{
    // This is just a placeholder for the API resource
}
```

#### 3.1.3 Create Connexion Processor
```php
<?php
// src/Processor/ConnexionProcessor.php
namespace App\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ConnexionInput;
use App\Dto\ConnexionOutput;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ConnexionProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private string $jwtSecret,
        private string $jwtAlgorithm = 'HS256'
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ConnexionOutput
    {
        /** @var ConnexionInput $data */
        $utilisateurRepository = $this->entityManager->getRepository(Utilisateur::class);
        $utilisateur = $utilisateurRepository->findOneBy(['email' => $data->email]);

        if (!$utilisateur || !$this->passwordHasher->isPasswordValid($utilisateur, $data->motDePasse)) {
            throw new UnauthorizedHttpException('Bearer', 'Identifiants invalides');
        }

        // Generate JWT token
        $payload = [
            'sub' => $utilisateur->getId(),
            'email' => $utilisateur->getEmail(),
            'iat' => time(),
            'exp' => time() + (24 * 60 * 60) // 24 hours
        ];

        $token = JWT::encode($payload, $this->jwtSecret, $this->jwtAlgorithm);

        return new ConnexionOutput(
            token: $token,
            utilisateurId: $utilisateur->getId(),
            nom: $utilisateur->getNom(),
            prenom: $utilisateur->getPrenom()
        );
    }
}
```

### 3.2 Idee Suppression Processor
```php
<?php
// src/Processor/IdeeSuppressionProcessor.php
namespace App\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Idee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class IdeeSuppressionProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Idee
    {
        $ideeRepository = $this->entityManager->getRepository(Idee::class);
        $idee = $ideeRepository->find($uriVariables['id']);

        if (!$idee) {
            throw new NotFoundHttpException('Idée non trouvée');
        }

        // Soft delete
        $idee->setSupprimee(true);
        $idee->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return $idee;
    }
}
```

### 3.3 Participant Occasion Processor
```php
<?php
// src/Processor/ParticipantOccasionProcessor.php
namespace App\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Occasion;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ParticipantOccasionProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $occasionRepository = $this->entityManager->getRepository(Occasion::class);
        $occasion = $occasionRepository->find($uriVariables['id']);

        if (!$occasion) {
            throw new NotFoundHttpException('Occasion non trouvée');
        }

        // Validate participation deadline
        if (new \DateTime() > $occasion->getDateLimiteParticipation()) {
            throw new BadRequestHttpException('La date limite de participation est dépassée');
        }

        $utilisateurIds = $data['utilisateurIds'] ?? [];
        
        if (empty($utilisateurIds)) {
            throw new BadRequestHttpException('Aucun participant spécifié');
        }

        $utilisateurRepository = $this->entityManager->getRepository(Utilisateur::class);
        $participants = [];

        foreach ($utilisateurIds as $utilisateurId) {
            $utilisateur = $utilisateurRepository->find($utilisateurId);
            if (!$utilisateur) {
                throw new NotFoundHttpException("Utilisateur {$utilisateurId} non trouvé");
            }
            $participants[] = $utilisateur;
        }

        // Here you would implement the logic to associate participants with the occasion
        // This might involve creating a ParticipantOccasion entity or updating existing records

        return [
            'occasion' => $occasion,
            'participants' => $participants,
            'message' => 'Participants ajoutés avec succès'
        ];
    }
}
```

### 3.4 Resultat Occasion Processor
```php
<?php
// src/Processor/ResultatOccasionProcessor.php
namespace App\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Occasion;
use App\Entity\Resultat;
use App\Entity\Utilisateur;
use App\Entity\Idee;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ResultatOccasionProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Resultat
    {
        $occasionRepository = $this->entityManager->getRepository(Occasion::class);
        $occasion = $occasionRepository->find($uriVariables['id']);

        if (!$occasion) {
            throw new NotFoundHttpException('Occasion non trouvée');
        }

        $donneurId = $data['donneurId'] ?? null;
        $receveurId = $data['receveurId'] ?? null;
        $ideeId = $data['ideeId'] ?? null;

        if (!$donneurId || !$receveurId) {
            throw new BadRequestHttpException('Donneur et receveur requis');
        }

        $utilisateurRepository = $this->entityManager->getRepository(Utilisateur::class);
        
        $donneur = $utilisateurRepository->find($donneurId);
        $receveur = $utilisateurRepository->find($receveurId);

        if (!$donneur || !$receveur) {
            throw new NotFoundHttpException('Utilisateur non trouvé');
        }

        $idee = null;
        if ($ideeId) {
            $ideeRepository = $this->entityManager->getRepository(Idee::class);
            $idee = $ideeRepository->find($ideeId);
        }

        $resultat = new Resultat();
        $resultat->setOccasion($occasion);
        $resultat->setDonneur($donneur);
        $resultat->setReceveur($receveur);
        $resultat->setIdee($idee);

        $this->entityManager->persist($resultat);
        $this->entityManager->flush();

        return $resultat;
    }
}
```

### 3.5 Tirage Occasion Processor
```php
<?php
// src/Processor/TirageOccasionProcessor.php
namespace App\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Occasion;
use App\Entity\Resultat;
use App\Entity\Utilisateur;
use App\Entity\Exclusion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TirageOccasionProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $occasionRepository = $this->entityManager->getRepository(Occasion::class);
        $occasion = $occasionRepository->find($uriVariables['id']);

        if (!$occasion) {
            throw new NotFoundHttpException('Occasion non trouvée');
        }

        // Get participants (this would depend on your ParticipantOccasion implementation)
        $participantIds = $data['participantIds'] ?? [];
        
        if (count($participantIds) < 2) {
            throw new BadRequestHttpException('Au moins 2 participants requis pour le tirage');
        }

        $utilisateurRepository = $this->entityManager->getRepository(Utilisateur::class);
        $participants = [];
        
        foreach ($participantIds as $participantId) {
            $participant = $utilisateurRepository->find($participantId);
            if (!$participant) {
                throw new NotFoundHttpException("Participant {$participantId} non trouvé");
            }
            $participants[] = $participant;
        }

        // Get exclusions
        $exclusionRepository = $this->entityManager->getRepository(Exclusion::class);
        $exclusions = $exclusionRepository->findAll();
        
        $exclusionMap = [];
        foreach ($exclusions as $exclusion) {
            $id1 = $exclusion->getUtilisateur1()->getId();
            $id2 = $exclusion->getUtilisateur2()->getId();
            $exclusionMap[$id1][] = $id2;
            $exclusionMap[$id2][] = $id1;
        }

        // Perform the gift draw algorithm
        $resultats = $this->performGiftDraw($participants, $exclusionMap);

        // Save results
        foreach ($resultats as $resultatData) {
            $resultat = new Resultat();
            $resultat->setOccasion($occasion);
            $resultat->setDonneur($resultatData['donneur']);
            $resultat->setReceveur($resultatData['receveur']);
            
            $this->entityManager->persist($resultat);
        }

        $this->entityManager->flush();

        return [
            'occasion' => $occasion,
            'resultats' => $resultats,
            'message' => 'Tirage effectué avec succès'
        ];
    }

    private function performGiftDraw(array $participants, array $exclusionMap): array
    {
        $maxAttempts = 1000;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $donneurs = array_values($participants);
            $receveurs = array_values($participants);
            shuffle($receveurs);

            $results = [];
            $valid = true;

            for ($i = 0; $i < count($donneurs); $i++) {
                $donneur = $donneurs[$i];
                $receveur = $receveurs[$i];

                // Check if donneur gives to themselves
                if ($donneur->getId() === $receveur->getId()) {
                    $valid = false;
                    break;
                }

                // Check exclusions
                $donneurId = $donneur->getId();
                $receveurId = $receveur->getId();
                
                if (isset($exclusionMap[$donneurId]) && in_array($receveurId, $exclusionMap[$donneurId])) {
                    $valid = false;
                    break;
                }

                $results[] = [
                    'donneur' => $donneur,
                    'receveur' => $receveur
                ];
            }

            if ($valid) {
                return $results;
            }

            $attempt++;
        }

        throw new BadRequestHttpException('Impossible de générer un tirage valide avec les exclusions actuelles');
    }
}
```

### 3.6 Reinit Mdp Processor
```php
<?php
// src/Processor/ReinitMdpProcessor.php
namespace App\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ReinitMdpProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private MailerInterface $mailer
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $utilisateurRepository = $this->entityManager->getRepository(Utilisateur::class);
        $utilisateur = $utilisateurRepository->find($uriVariables['id']);

        if (!$utilisateur) {
            throw new NotFoundHttpException('Utilisateur non trouvé');
        }

        // Generate new temporary password
        $newPassword = $this->generateRandomPassword();
        $hashedPassword = $this->passwordHasher->hashPassword($utilisateur, $newPassword);
        
        $utilisateur->setMotDePasse($hashedPassword);
        $utilisateur->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        // Send email with new password
        $email = (new Email())
            ->from('noreply@yourapp.com')
            ->to($utilisateur->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->text("Votre nouveau mot de passe est : {$newPassword}");

        $this->mailer->send($email);

        return [
            'message' => 'Nouveau mot de passe envoyé par email',
            'email' => $utilisateur->getEmail()
        ];
    }

    private function generateRandomPassword(int $length = 12): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*';
        return substr(str_shuffle(str_repeat($characters, ceil($length / strlen($characters)))), 0, $length);
    }
}
```

### 3.7 Exclusion Processor
```php
<?php
// src/Processor/ExclusionProcessor.php
namespace App\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Exclusion;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExclusionProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Exclusion
    {
        $utilisateurRepository = $this->entityManager->getRepository(Utilisateur::class);
        $utilisateur1 = $utilisateurRepository->find($uriVariables['id']);

        if (!$utilisateur1) {
            throw new NotFoundHttpException('Utilisateur non trouvé');
        }

        $utilisateur2Id = $data['utilisateur2Id'] ?? null;
        
        if (!$utilisateur2Id) {
            throw new BadRequestHttpException('ID du second utilisateur requis');
        }

        if ($utilisateur1->getId() === $utilisateur2Id) {
            throw new BadRequestHttpException('Un utilisateur ne peut pas s\'exclure lui-même');
        }

        $utilisateur2 = $utilisateurRepository->find($utilisateur2Id);
        
        if (!$utilisateur2) {
            throw new NotFoundHttpException('Second utilisateur non trouvé');
        }

        // Check if exclusion already exists
        $exclusionRepository = $this->entityManager->getRepository(Exclusion::class);
        $existingExclusion = $exclusionRepository->createQueryBuilder('e')
            ->where('(e.utilisateur1 = :u1 AND e.utilisateur2 = :u2) OR (e.utilisateur1 = :u2 AND e.utilisateur2 = :u1)')
            ->setParameter('u1', $utilisateur1)
            ->setParameter('u2', $utilisateur2)
            ->getQuery()
            ->getOneOrNullResult();

        if ($existingExclusion) {
            throw new BadRequestHttpException('Cette exclusion existe déjà');
        }

        $exclusion = new Exclusion();
        $exclusion->setUtilisateur1($utilisateur1);
        $exclusion->setUtilisateur2($utilisateur2);

        $this->entityManager->persist($exclusion);
        $this->entityManager->flush();

        return $exclusion;
    }
}
```

## Step 4: Create State Providers

### 4.1 Idee Collection Provider
```php
<?php
// src/Provider/IdeeCollectionProvider.php
namespace App\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Idee;
use Doctrine\ORM\EntityManagerInterface;

class IdeeCollectionProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $ideeRepository = $this->entityManager->getRepository(Idee::class);
        
        // Only return non-deleted ideas
        return $ideeRepository->createQueryBuilder('i')
            ->where('i.supprimee = :supprimee')
            ->setParameter('supprimee', false)
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
```

### 4.2 Exclusion Collection Provider
```php
<?php
// src/Provider/ExclusionCollectionProvider.php
namespace App\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Exclusion;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ExclusionCollectionProvider implements ProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $utilisateurRepository = $this->entityManager->getRepository(Utilisateur::class);
        $utilisateur = $utilisateurRepository->find($uriVariables['id']);

        if (!$utilisateur) {
            throw new NotFoundHttpException('Utilisateur non trouvé');
        }

        $exclusionRepository = $this->entityManager->getRepository(Exclusion::class);
        
        return $exclusionRepository->createQueryBuilder('e')
            ->where('e.utilisateur1 = :utilisateur OR e.utilisateur2 = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
```

## Step 5: Configuration

### 5.1 Security Configuration
```yaml
# config/packages/security.yaml
security:
    password_hashers:
        App\Entity\Utilisateur: 'auto'
    
    providers:
        app_user_provider:
            entity:
                class: App\Entity\Utilisateur
                property: email
    
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false
            
        api:
            pattern: ^/api/
            stateless: true
            jwt: ~
            
        main:
            lazy: true
            provider: app_user_provider
            json_login:
                check_path: /api/connexion
                success_handler: lexik_jwt_authentication.handler.authentication_success
                failure_handler: lexik_jwt_authentication.handler.authentication_failure

    access_control:
        - { path: ^/api/connexion, roles: PUBLIC_ACCESS }
        - { path: ^/api/docs, roles: PUBLIC_ACCESS }
        - { path: ^/api, roles: IS_AUTHENTICATED_FULLY }
```

### 5.2 JWT Configuration
```yaml
# config/packages/lexik_jwt_authentication.yaml
lexik_jwt_authentication:
    secret_key: '%env(resolve:JWT_SECRET_KEY)%'
    public_key: '%env(resolve:JWT_PUBLIC_KEY)%'
    pass_phrase: '%env(JWT_PASSPHRASE)%'
```

### 5.3 Services Configuration
```yaml
# config/services.yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true

    App\:
        resource: '../src/'
        exclude:
            - '../src/DependencyInjection/'
            - '../src/Entity/'
            - '../src/Kernel.php'

    App\Processor\ConnexionProcessor:
        arguments:
            $jwtSecret: '%env(JWT_SECRET_KEY)%'
```

## Step 6: Database Migration

### 6.1 Create Migration
```bash
php bin/console make:migration
```

### 6.2 Run Migration
```bash
php bin/console doctrine:migrations:migrate
```

## Step 7: Testing the API

### 7.1 Start the Server
```bash
symfony server:start
```

### 7.2 Test Custom Operations

#### Authentication
```bash
curl -X POST http://localhost:8000/api/connexion \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "motDePasse": "password"}'
```

#### Soft Delete Idee
```bash
curl -X POST http://localhost:8000/api/idees/123/suppression \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

#### Add Participants to Occasion
```bash
curl -X POST http://localhost:8000/api/occasions/123/participants \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"utilisateurIds": ["user1-id", "user2-id"]}'
```

#### Generate Gift Draw
```bash
curl -X POST http://localhost:8000/api/occasions/123/tirage \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"participantIds": ["user1-id", "user2-id", "user3-id"]}'
```

#### Reset Password
```bash
curl -X POST http://localhost:8000/api/utilisateurs/123/reinitmdp \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

#### Create Exclusion
```bash
curl -X POST http://localhost:8000/api/utilisateurs/123/exclusions \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"utilisateur2Id": "user2-id"}'
```

## Summary

This migration guide transforms your Slim-based API into a modern API Platform application using **processors** for all custom operations:

### Custom Operations Implemented:
1. **ConnexionProcessor** - Authentication with JWT
2. **IdeeSuppressionProcessor** - Soft delete for ideas
3. **ParticipantOccasionProcessor** - Add participants to occasions
4. **ResultatOccasionProcessor** - Create gift results
5. **TirageOccasionProcessor** - Generate gift draw with exclusion logic
6. **ReinitMdpProcessor** - Password reset with email
7. **ExclusionProcessor** - Create user exclusions

### State Providers:
1. **IdeeCollectionProvider** - Filter out deleted ideas
2. **ExclusionCollectionProvider** - Get user exclusions

### Benefits of Using Processors:
- **Clean Architecture**: Separates business logic from HTTP concerns
- **Reusability**: Processors can be reused across different operations
- **Testability**: Easier to unit test business logic
- **API Platform Integration**: Full integration with API Platform features
- **Automatic Documentation**: OpenAPI documentation generated automatically
- **Security Integration**: Seamless integration with Symfony Security

This approach maintains all your existing functionality while providing the benefits of API Platform's modern architecture and automatic API documentation.
