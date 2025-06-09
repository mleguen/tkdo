<?php

// Updated Entity configurations with custom operations

// src/Entity/Utilisateur.php
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put(),
        new Delete(),
        // Custom operation for password reset
        new Post(
            uriTemplate: '/utilisateurs/{id}/reinitmdp',
            processor: ReinitMdpProcessor::class,
            name: 'reinit_mdp'
        ),
    ],
    normalizationContext: ['groups' => ['utilisateur:read']],
    denormalizationContext: ['groups' => ['utilisateur:write']]
)]
class Utilisateur
{
    // ... entity properties and methods
}

// src/Entity/Idee.php
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put(),
        new Delete(),
        // Custom operation for soft delete (suppression)
        new Post(
            uriTemplate: '/idees/{id}/suppression',
            processor: IdeeSuppressionProcessor::class,
            name: 'suppression_idee'
        ),
    ],
    normalizationContext: ['groups' => ['idee:read']],
    denormalizationContext: ['groups' => ['idee:write']]
)]
class Idee
{
    // ... entity properties and methods
}

// src/Entity/Occasion.php
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(),
        new Get(),
        new Put(),
        new Delete(),
        // Custom operation for adding participants
        new Post(
            uriTemplate: '/occasions/{id}/participant',
            processor: ParticipantOccasionProcessor::class,
            name: 'add_participant'
        ),
        // Custom operation for adding results
        new Post(
            uriTemplate: '/occasions/{id}/resultat',
            processor: ResultatOccasionProcessor::class,
            name: 'add_resultat'
        ),
        // Custom operation for tirage (draw)
        new Post(
            uriTemplate: '/occasions/{id}/tirage',
            processor: TirageOccasionProcessor::class,
            name: 'tirage'
        ),
    ],
    normalizationContext: ['groups' => ['occasion:read']],
    denormalizationContext: ['groups' => ['occasion:write']]
)]
class Occasion
{
    // ... entity properties and methods
}

// src/Entity/Exclusion.php
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/utilisateurs/{idUtilisateur}/exclusions',
            provider: ExclusionCollectionProvider::class
        ),
        new Post(
            uriTemplate: '/utilisateurs/{idUtilisateur}/exclusions',
            processor: ExclusionProcessor::class
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
    // ... entity properties and methods
}

// Custom DTO for connexion
// src/Dto/ConnexionInput.php
class ConnexionInput
{
    public string $identifiant;
    public string $mdp;
}

// src/Dto/ConnexionOutput.php
class ConnexionOutput
{
    public string $token;
    public Utilisateur $utilisateur;
}

// Connexion as a standalone resource
// src/Resource/Connexion.php
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/connexion',
            input: ConnexionInput::class,
            output: ConnexionOutput::class,
            processor: ConnexionProcessor::class,
            name: 'connexion'
        ),
    ]
)]
class Connexion
{
    // Empty class - just a placeholder for the operation
}
