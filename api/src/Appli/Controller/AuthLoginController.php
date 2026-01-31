<?php

declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\Service\RouteService;
use App\Dom\Exception\UtilisateurInconnuException;
use App\Dom\Repository\AuthCodeRepository;
use App\Dom\Repository\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

class AuthLoginController
{
    public function __construct(
        private readonly AuthCodeRepository $authCodeRepository,
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

            // Create auth code with 60 second expiry
            $result = $this->authCodeRepository->create($utilisateur->getId(), 60);

            $this->logger->info("Auth code créé pour utilisateur {$utilisateur->getId()} ({$utilisateur->getNom()})");

            // Return only the code, not the JWT or user data
            return $this->routeService->getResponseWithJsonBody($response, json_encode([
                'code' => $result['code'],
            ], JSON_THROW_ON_ERROR));
        } catch (UtilisateurInconnuException) {
            throw new HttpBadRequestException($request, 'identifiants invalides');
        }
    }
}
