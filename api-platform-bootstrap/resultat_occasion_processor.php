<?php

// src/Processor/ResultatOccasionProcessor.php
namespace App\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Occasion;
use App\Entity\Resultat;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;

class ResultatOccasionProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UtilisateurRepository $utilisateurRepository,
        private Security $security
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Resultat
    {
        $occasion = $this->entityManager->getRepository(Occasion::class)->find($uriVariables['id']);
        
        if (!$occasion) {
            throw new BadRequestException('Occasion introuvable');
        }

        $currentUser = $this->security->getUser();
        
        // Only admin can add results
        if (!$currentUser->isAdmin()) {
            throw new AccessDeniedHttpException('Seuls les administrateurs peuvent ajouter des résultats');
        }

        $request = $context['request'] ?? null;
        if (!$request instanceof Request) {
            throw new BadRequestException('Invalid request');
        }

        $requestData = json_decode($request->getContent(), true);
        $quiOffreId = $requestData['idQuiOffre'] ?? null;
        $quiRecoitId = $requestData['idQuiRecoit'] ?? null;

        if (!$quiOffreId || !$quiRecoitId) {
            throw new BadRequestException('IDs des participants requis');
        }

        $quiOffre = $this->utilisateurRepository->find($quiOffreId);
        $quiRecoit = $this->utilisateurRepository->find($quiRecoitId);

        if (!$quiOffre || !$quiRecoit) {
            throw new BadRequestException('Participants introuvables');
        }

        // Check if participants are part of the occasion
        if (!$occasion->getParticipants()->contains($quiOffre) || 
            !$occasion->getParticipants()->contains($quiRecoit)) {
            throw new BadRequestException('Les participants doivent faire partie de l\'occasion');
        }

        // Create new result
        $resultat = new Resultat();
        $resultat->setOccasion($occasion);
        $resultat->setQuiOffre($quiOffre);
        $resultat->setQuiRecoit($quiRecoit);

        $this->entityManager->persist($resultat);
        $this->entityManager->flush();

        return $resultat;
    }
}
