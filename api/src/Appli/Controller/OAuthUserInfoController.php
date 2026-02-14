<?php

// TEMPORARY: Will be replaced by external IdP

declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\ModelAdaptor\AuthAdaptor;
use App\Appli\Service\RouteService;
use App\Dom\Repository\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpUnauthorizedException;

/**
 * TEMPORARY: OAuth2 Authorization Server — userinfo endpoint.
 * Will be replaced by external IdP (Google, Auth0, etc.) post-MVP.
 *
 * GET: Returns user claims for a valid Bearer access token.
 *      Standard OAuth2/OIDC pattern used by league/oauth2-client GenericProvider.
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
        // AuthMiddleware already validated the Bearer token and set auth attribute
        /** @var AuthAdaptor|null $auth */
        $auth = $request->getAttribute('auth');
        if ($auth === null) {
            throw new HttpUnauthorizedException($request, 'token invalide ou manquant');
        }

        $utilisateur = $this->utilisateurRepository->read($auth->getIdUtilisateur());

        return $this->routeService->getResponseWithJsonBody($response, json_encode([
            'sub' => $utilisateur->getId(),
            'nom' => $utilisateur->getNom(),
            'email' => $utilisateur->getEmail(),
            'genre' => $utilisateur->getGenre(),
            'admin' => $utilisateur->getAdmin(),
            'groupe_ids' => $auth->getGroupeIds(),
        ], JSON_THROW_ON_ERROR));
    }
}
