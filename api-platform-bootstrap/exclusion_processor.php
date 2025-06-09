<?php

// src/Processor/ExclusionProcessor.php
namespace App\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Exclusion;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;

class ExclusionProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UtilisateurRepository $utilisateurRepository,
        private Security $security
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Exclusion
    {
        $quiOffre = $this->utilisateurRepository->find($uriVariables['idUtilisateur']);
        
        if (!$quiOffre) {
            throw new BadRequestException('Utilisateur introuvable');
        }

        $currentUser = $this->security->getUser();
        
        // Only admin can create exclusions
        if (!$currentUser->isAdmin()) {
            throw new AccessDeniedHttpException('Seuls les administrateurs peuvent créer des exclusions');
        }

        $request = $context['request'] ?? null;
        if (!$request instanceof Request) {
            throw new BadRequestException('Invalid request');
        }

        $requestData = json_decode($request->getContent(), true);
        $quiNeDoitPasRecevoirId = $requestData['idQuiNeDoitPasRecevoir'] ?? null;

        if (!$quiNeDoitPasRecevoirId) {
            throw new BadRequestException('ID de la personne à exclure requis');
        }

        $quiNeDoitPasRecevoir = $this->utilisateurRepository->find($quiNeDoitPasRecevoirId);
        
        if (!$quiNeDoitPasRecevoir) {
            throw new BadRequestException('Personne à exclure introuvable');
        }

        // Check if exclusion already exists
        $existingExclusion = $this->entityManager->getRepository(Exclusion::class)
            ->findOneBy([
                'quiOffre' => $quiOffre,
                'quiNeDoitPasRecevoir' => $quiNeDoitPasRecevoir
            ]);

        if ($existingExclusion) {
            throw new BadRequestException('Cette exclusion existe déjà');
        }

        // Create new exclusion
        $exclusion = new Exclusion();
        $exclusion->setQuiOffre($quiOffre);
        $exclusion->setQuiNeDoitPasRecevoir($quiNeDoitPasRecevoir);

        $this->entityManager->persist($exclusion);
        $this->entityManager->flush();

        return $exclusion;
    }
}
