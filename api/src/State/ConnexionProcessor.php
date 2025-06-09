<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\ConnexionInput;
use App\Dto\ConnexionOutput;
use App\Repository\UtilisateurRepository;
use Firebase\JWT\JWT;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ConnexionProcessor implements ProcessorInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private UtilisateurRepository $utilisateurRepository,
        private string $jwtSecret,
        private string $jwtAlgorithm = 'HS256'
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ConnexionOutput
    {
        /** @var ConnexionInput $data */
        $utilisateur = $this->utilisateurRepository->findByEmail($data->email);

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
