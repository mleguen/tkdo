<?php

// src/Provider/ExclusionCollectionProvider.php
namespace App\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Exclusion;
use App\Repository\ExclusionRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class ExclusionCollectionProvider implements ProviderInterface
{
    public function __construct(
        private ExclusionRepository $exclusionRepository,
        private UtilisateurRepository $utilisateurRepository,
        private Security $security
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $utilisateur = $this->utilisateurRepository->find($uriVariables['idUtilisateur']);
        
        if (!$utilisateur) {
            throw new BadRequestException('Utilisateur introuvable');
        }

        $currentUser = $this->security->getUser();
        
        // Only admin can view exclusions
        if (!$currentUser->isAdmin()) {
            throw new AccessDeniedHttpException('Seuls les administrateurs peuvent voir les exclusions');
        }

        return $this->exclusionRepository->findBy(['quiOffre' => $utilisateur]);
    }
}

// src/Provider/IdeeCollectionProvider.php
namespace App\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\IdeeRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;

class IdeeCollectionProvider implements ProviderInterface
{
    public function __construct(
        private IdeeRepository $ideeRepository,
        private UtilisateurRepository $utilisateurRepository,
        private Security $security
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $request = $context['request'] ?? null;
        if (!$request instanceof Request) {
            throw new BadRequestException('Invalid request');
        }

        $idUtilisateur = $request->query->get('idUtilisateur');
        $supprimees = $request->query->get('supprimees');

        if (!$idUtilisateur) {
            throw new BadRequestException('ID utilisateur requis');
        }

        $utilisateur = $this->utilisateurRepository->find($idUtilisateur);
        if (!$utilisateur) {
            throw new BadRequestException('Utilisateur introuvable');
        }

        $currentUser = $this->security->getUser();
        
        // Convert supprimees parameter
        $includeSupprimees = null;
        if ($supprimees !== null) {
            $includeSupprimees = (bool) $supprimees;
            
            // Only admin can see deleted ideas
            if ($includeSupprimees && !$currentUser->isAdmin()) {
                throw new AccessDeniedHttpException('Seuls les administrateurs peuvent voir les idées supprimées');
            }
        }

        // Get ideas for the user
        $idees = $this->ideeRepository->findByUtilisateur($utilisateur, $includeSupprimees);

        // Filter based on authorization
        return array_filter($idees, function($idee) use ($currentUser) {
            // User can see their own ideas or ideas proposed by others for them
            return $idee->getAuteur() === $currentUser || 
                   $idee->getUtilisateur() !== $currentUser;
        });
    }
}
