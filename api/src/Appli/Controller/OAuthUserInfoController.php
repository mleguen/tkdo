<?php

// TEMPORARY: Will be replaced by external IdP

declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\Service\RouteService;
use App\Dom\Exception\UtilisateurInconnuException;
use App\Dom\Repository\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpUnauthorizedException;

/**
 * TEMPORARY: OAuth2 Authorization Server — userinfo endpoint.
 * Will be replaced by external IdP (Google, Auth0, etc.) post-MVP.
 *
 * GET: Returns standard OIDC claims (sub, name, email) from the access token.
 *      Application-specific data (admin, genre, groups) is loaded
 *      by BffAuthCallbackController from the database, not from IdP claims.
 */
class OAuthUserInfoController
{
    public function __construct(
        private readonly RouteService $routeService,
        private readonly UtilisateurRepository $utilisateurRepository
    ) {
    }

    /**
     * @param array<string, mixed> $args
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        // Use RouteService.getAuth() for proper error handling (checks authErr attribute)
        $auth = $this->routeService->getAuth($request);

        try {
            $utilisateur = $this->utilisateurRepository->read($auth->getIdUtilisateur());
        } catch (UtilisateurInconnuException) {
            throw new HttpUnauthorizedException($request, 'utilisateur inconnu');
        }

        // Standard OIDC userinfo claims only — no app-specific data
        return $this->routeService->getResponseWithJsonBody($response, json_encode([
            'sub' => $utilisateur->getId(),
            'name' => $utilisateur->getNom(),
            'email' => $utilisateur->getEmail(),
        ], JSON_THROW_ON_ERROR));
    }
}
