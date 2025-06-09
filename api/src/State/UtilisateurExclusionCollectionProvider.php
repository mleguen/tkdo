<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Utilisateur;
use App\Repository\ExclusionRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UtilisateurExclusionCollectionProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $utilisateurProvider,
        private ExclusionRepository $exclusionRepository
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Get the utilisateur using the standard provider
        $utilisateur = $this->utilisateurProvider->provide($operation, $uriVariables, $context);
        
        if (!$utilisateur instanceof Utilisateur) {
            throw new NotFoundHttpException('Utilisateur non trouvé');
        }

        return $this->exclusionRepository->findByUtilisateur($utilisateur);
    }
}
