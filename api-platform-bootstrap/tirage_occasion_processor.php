<?php

// src/Processor/TirageOccasionProcessor.php
namespace App\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Exclusion;
use App\Entity\Occasion;
use App\Entity\Resultat;
use App\Repository\ExclusionRepository;
use App\Repository\ResultatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class TirageOccasionProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ExclusionRepository $exclusionRepository,
        private ResultatRepository $resultatRepository,
        private Security $security
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $occasion = $this->entityManager->getRepository(Occasion::class)->find($uriVariables['id']);
        
        if (!$occasion) {
            throw new BadRequestException('Occasion introuvable');
        }

        $currentUser = $this->security->getUser();
        
        // Only admin can perform tirage
        if (!$currentUser->isAdmin()) {
            throw new AccessDeniedHttpException('Seuls les administrateurs peuvent effectuer le tirage');
        }

        // Check if tirage already done
        $existingResults = $this->resultatRepository->findBy(['occasion' => $occasion]);
        if (!empty($existingResults)) {
            throw new BadRequestException('Le tirage a déjà été effectué pour cette occasion');
        }

        $participants = $occasion->getParticipants()->toArray();
        $participantCount = count($participants);

        if ($participantCount < 2) {
            throw new BadRequestException('Il faut au moins 2 participants pour effectuer un tirage');
        }

        // Get all exclusions for this occasion
        $exclusions = $this->exclusionRepository->readByParticipantsOccasion($occasion);
        $exclusionMap = [];
        
        foreach ($exclusions as $exclusion) {
            $quiOffreId = $exclusion->getQuiOffre()->getId();
            $quiNeDoitPasRecevoirId = $exclusion->getQuiNeDoitPasRecevoir()->getId();
            
            if (!isset($exclusionMap[$quiOffreId])) {
                $exclusionMap[$quiOffreId] = [];
            }
            $exclusionMap[$quiOffreId][] = $quiNeDoitPasRecevoirId;
        }

        // Perform the draw using a simple algorithm
        $resultats = $this->performTirage($participants, $exclusionMap);

        // Save results
        foreach ($resultats as $resultatData) {
            $resultat = new Resultat();
            $resultat->setOccasion($occasion);
            $resultat->setQuiOffre($resultatData['quiOffre']);
            $resultat->setQuiRecoit($resultatData['quiRecoit']);
            
            $this->entityManager->persist($resultat);
        }

        $this->entityManager->flush();

        return $resultats;
    }

    private function performTirage(array $participants, array $exclusionMap): array
    {
        $participantIds = array_map(fn($p) => $p->getId(), $participants);
        $participantById = array_combine($participantIds, $participants);
        $availableReceivers = $participantIds;
        $results = [];

        // Shuffle participants for random order
        shuffle($participantIds);

        foreach ($participantIds as $giverId) {
            $giver = $participantById[$giverId];
            $excludedIds = $exclusionMap[$giverId] ?? [];
            
            // Remove giver from available receivers (can't give to themselves)
            $excludedIds[] = $giverId;
            
            // Filter available receivers
            $possibleReceivers = array_diff($availableReceivers, $excludedIds);
            
            if (empty($possibleReceivers)) {
                // If no valid receiver found, we need to backtrack or use a more sophisticated algorithm
                throw new BadRequestException('Impossible d\'effectuer un tirage valide avec les exclusions actuelles');
            }
            
            // Pick a random receiver
            $receiverId = $possibleReceivers[array_rand($possibleReceivers)];
            $receiver = $participantById[$receiverId];
            
            $results[] = [
                'quiOffre' => $giver,
                'quiRecoit' => $receiver
            ];
            
            // Remove this receiver from available list
            $availableReceivers = array_diff($availableReceivers, [$receiverId]);
        }

        return $results;
    }
}
