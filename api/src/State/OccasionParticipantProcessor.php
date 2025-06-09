<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use App\Dto\AddParticipantsInput;
use App\Entity\Occasion;
use App\Repository\UtilisateurRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OccasionParticipantProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $occasionProvider,
        private UtilisateurRepository $utilisateurRepository
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): array
    {
        // Get the occasion using the standard provider (check entity exists first)
        $occasion = $this->occasionProvider->provide($operation, $uriVariables, $context);
        
        if (!$occasion instanceof Occasion) {
            throw new NotFoundHttpException('Occasion non trouvée');
        }

        /** @var AddParticipantsInput $data */
        if (!$data instanceof AddParticipantsInput) {
            throw new BadRequestHttpException('Invalid input data');
        }

        // Validate participation deadline
        if (new \DateTime() > $occasion->getDateLimiteParticipation()) {
            throw new BadRequestHttpException('La date limite de participation est dépassée');
        }

        $utilisateurIds = $data->utilisateurIds;

        if (empty($utilisateurIds)) {
            throw new BadRequestHttpException('Aucun participant spécifié');
        }

        $participants = [];

        foreach ($utilisateurIds as $utilisateurId) {
            $utilisateur = $this->utilisateurRepository->find($utilisateurId);
            if (!$utilisateur) {
                throw new NotFoundHttpException("Utilisateur {$utilisateurId} non trouvé");
            }
            $participants[] = $utilisateur;
        }

        // Here you would implement the logic to associate participants with the occasion
        // This might involve creating a ParticipantOccasion entity or updating existing records

        return [
            'occasion' => $occasion,
            'participants' => $participants,
            'message' => 'Participants ajoutés avec succès'
        ];
    }
}
