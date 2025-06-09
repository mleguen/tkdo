<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\State\ProviderInterface;
use App\Dto\CreateExclusionInput;
use App\Entity\Exclusion;
use App\Entity\Utilisateur;
use App\Repository\ExclusionRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UtilisateurExclusionProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private ProviderInterface $utilisateurProvider,
        private ExclusionRepository $exclusionRepository,
        private UtilisateurRepository $utilisateurRepository
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Exclusion
    {
        // Get the first utilisateur using the standard provider (check entity exists first)
        $utilisateur1 = $this->utilisateurProvider->provide($operation, $uriVariables, $context);
        
        if (!$utilisateur1 instanceof Utilisateur) {
            throw new NotFoundHttpException('Utilisateur non trouvé');
        }

        /** @var CreateExclusionInput $data */
        if (!$data instanceof CreateExclusionInput) {
            throw new BadRequestHttpException('Invalid input data');
        }

        $utilisateur2Id = $data->utilisateur2Id;

        if ($utilisateur1->getId()->toString() === $utilisateur2Id) {
            throw new BadRequestHttpException('Un utilisateur ne peut pas s\'exclure lui-même');
        }

        $utilisateur2 = $this->utilisateurRepository->find($utilisateur2Id);

        if (!$utilisateur2) {
            throw new NotFoundHttpException('Second utilisateur non trouvé');
        }

        // Check if exclusion already exists
        $existingExclusion = $this->exclusionRepository->findExistingExclusion($utilisateur1, $utilisateur2);

        if ($existingExclusion) {
            throw new BadRequestHttpException('Cette exclusion existe déjà');
        }

        $exclusion = new Exclusion();
        $exclusion->setUtilisateur1($utilisateur1);
        $exclusion->setUtilisateur2($utilisateur2);

        $this->entityManager->persist($exclusion);
        $this->entityManager->flush();

        return $exclusion;
    }
}
