<?php

// src/Processor/ReinitMdpProcessor.php
namespace App\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Utilisateur;
use App\Service\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ReinitMdpProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private MailService $mailService,
        private Security $security
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Utilisateur
    {
        $utilisateur = $this->entityManager->getRepository(Utilisateur::class)->find($uriVariables['id']);
        
        if (!$utilisateur) {
            throw new BadRequestException('Utilisateur introuvable');
        }

        $currentUser = $this->security->getUser();
        
        // Only admin can reset passwords
        if (!$currentUser->isAdmin()) {
            throw new AccessDeniedHttpException('Seuls les administrateurs peuvent réinitialiser les mots de passe');
        }

        // Generate new random password
        $newPassword = $this->generateRandomPassword();
        
        // Hash and set new password
        $hashedPassword = $this->passwordHasher->hashPassword($utilisateur, $newPassword);
        $utilisateur->setMdp($hashedPassword);
        
        // Send email with new password
        $this->mailService->sendPasswordReset($utilisateur, $newPassword);
        
        $this->entityManager->flush();

        return $utilisateur;
    }

    private function generateRandomPassword(int $length = 12): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $password;
    }
}
