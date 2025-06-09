<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use App\Dto\CreateTirageInput;
use App\Entity\Occasion;
use App\Entity\Resultat;
use App\Repository\ExclusionRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OccasionTirageProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $occasionProvider,
        private ExclusionRepository $exclusionRepository,
        private UtilisateurRepository $utilisateurRepository
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        // Get the occasion using the standard provider (check entity exists first)
        $occasion = $this->occasionProvider->provide($operation, $uriVariables, $context);
        
        if (!$occasion instanceof Occasion) {
            throw new NotFoundHttpException('Occasion non trouvée');
        }

        /** @var CreateTirageInput $data */
        if (!$data instanceof CreateTirageInput) {
            throw new BadRequestHttpException('Invalid input data');
        }

        $participantIds = $data->participantIds;

        if (count($participantIds) < 2) {
            throw new BadRequestHttpException('Au moins 2 participants requis pour le tirage');
        }

        $participants = [];

        foreach ($participantIds as $participantId) {
            $participant = $this->utilisateurRepository->find($participantId);
            if (!$participant) {
                throw new NotFoundHttpException("Participant {$participantId} non trouvé");
            }
            $participants[] = $participant;
        }

        // Get exclusions
        $exclusions = $this->exclusionRepository->findAllExclusions();

        $exclusionMap = [];
        foreach ($exclusions as $exclusion) {
            $id1 = $exclusion->getUtilisateur1()->getId()->toString();
            $id2 = $exclusion->getUtilisateur2()->getId()->toString();
            $exclusionMap[$id1][] = $id2;
            $exclusionMap[$id2][] = $id1;
        }

        // Perform the gift draw algorithm
        $resultats = $this->performGiftDraw($participants, $exclusionMap);

        if (!$resultats) {
            throw new BadRequestHttpException('Impossible de réaliser le tirage avec les exclusions actuelles');
        }

        // Save results
        foreach ($resultats as $resultat) {
            $this->entityManager->persist($resultat);
        }
        $this->entityManager->flush();

        return [
            'occasion' => $occasion,
            'resultats' => $resultats,
            'message' => 'Tirage réalisé avec succès'
        ];
    }

    private function performGiftDraw(array $participants, array $exclusionMap): ?array
    {
        $resultats = [];
        $available = array_map(fn($p) => $p->getId()->toString(), $participants);
        $participantMap = [];
        
        foreach ($participants as $participant) {
            $participantMap[$participant->getId()->toString()] = $participant;
        }

        foreach ($participants as $donneur) {
            $donneurId = $donneur->getId()->toString();
            $excluded = $exclusionMap[$donneurId] ?? [];
            $excluded[] = $donneurId; // Can't give to self
            
            $possible = array_diff($available, $excluded);
            
            if (empty($possible)) {
                return null; // Impossible draw
            }
            
            $receveurId = $possible[array_rand($possible)];
            $receveur = $participantMap[$receveurId];
            
            $resultat = new Resultat();
            $resultat->setDonneur($donneur);
            $resultat->setReceveur($receveur);
            
            $resultats[] = $resultat;
            
            // Remove chosen recipient from available
            $available = array_filter($available, fn($id) => $id !== $receveurId);
        }
        
        return $resultats;
    }
}
