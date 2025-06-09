<?php

// src/Processor/ConnexionProcessor.php
namespace App\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ConnexionInput;
use App\Dto\ConnexionOutput;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ConnexionProcessor implements ProcessorInterface
{
    public function __construct(
        private UtilisateurRepository $utilisateurRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ConnexionOutput
    {
        /** @var ConnexionInput $data */
        
        // Find user by identifiant
        $utilisateur = $this->utilisateurRepository->findOneBy(['identifiant' => $data->identifiant]);
        
        if (!$utilisateur || !$this->passwordHasher->isPasswordValid($utilisateur, $data->mdp)) {
            throw new BadRequestException('Invalid credentials');
        }

        // Generate JWT token
        $token = $this->jwtManager->create($utilisateur);

        $output = new ConnexionOutput();
        $output->token = $token;
        $output->utilisateur = $utilisateur;

        return $output;
    }
}
