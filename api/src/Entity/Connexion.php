<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Dto\ConnexionInput;
use App\Dto\ConnexionOutput;
use App\State\ConnexionProcessor;

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
