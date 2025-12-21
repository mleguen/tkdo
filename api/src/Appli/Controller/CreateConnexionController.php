<?php

declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\Service\JsonService;
use App\Appli\Service\RouteService;
use App\Dom\Exception\UtilisateurInconnuException;
use App\Dom\Repository\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

class CreateConnexionController
{
    public function __construct(
        private readonly JsonService $jsonService,
        private readonly LoggerInterface $logger,
        private readonly RouteService $routeService,
        private readonly UtilisateurRepository $utilisateurRepository
    ) {
    }

    /**
     * @param array<string, mixed> $args
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = $this->routeService->getParsedRequestBody($request, ['identifiant', 'mdp']);

        try {
            $utilisateur = $this->utilisateurRepository->readOneByIdentifiant($body['identifiant']);
            if (!$utilisateur->verifieMdp($body['mdp'])) {
                throw new UtilisateurInconnuException();
            }

            $this->logger->info("Utilisateur {$utilisateur->getId()} ({$utilisateur->getNom()}) connecté" . ($utilisateur->getAdmin() ? ' (admin)' : ''));

            return $this->routeService->getResponseWithJsonBody($response, $this->jsonService->encodeConnexion($utilisateur));
        } catch (UtilisateurInconnuException) {
            throw new HttpBadRequestException($request, 'identifiants invalides');
        }
    }
}
