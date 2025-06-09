<?php

// src/Processor/ParticipantOccasionProcessor.php
namespace App\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Occasion;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;

class ParticipantOccasionProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UtilisateurRepository $utilisateurRepository,
        private Security $security
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Occasion
    {
        $occasion = $this->entityManager->getRepository(Occasion::class)->find($uriVariables['id']);
        
        if (!$occasion) {
            throw new BadRequestException('Occasion introuvable');
        }

        $currentUser = $this->security->getUser();
        
        // Only admin can add participants
        if (!$currentUser->isAdmin()) {
            throw new AccessDeniedHttpException('Seuls les administrateurs peuvent ajouter des participants');
        }

        // Get participant ID from request data
        $request = $context['request'] ?? null;
        if (!$request instanceof Request) {
            throw new BadRequestException('Invalid request');
        }

        $requestData = json_decode($request->getContent(), true);
        $participantId = $requestData['idParticipant'] ?? null;

        if (!$participantId) {
            throw new BadRequestException('ID du participant requis');
        }

        $participant = $this->utilisateurRepository->find($participantId);
        
        if (!$participant) {
            throw new BadRequestException('Participant introuvable');
        }

        // Add participant if not already added
        if (!$occasion->getParticipants()->contains($participant)) {
            $occasion->addParticipant($participant);
            $this->entityManager->flush();
        }

        return $occasion;
    }
}
