<?php

// TEMPORARY: Will be replaced by external IdP

declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\Service\RouteService;
use App\Appli\Settings\OAuth2Settings;
use App\Dom\Exception\UtilisateurInconnuException;
use App\Dom\Repository\AuthCodeRepository;
use App\Dom\Repository\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

/**
 * TEMPORARY: OAuth2 Authorization Server — authorize endpoint.
 * Will be replaced by external IdP (Google, Auth0, etc.) post-MVP.
 *
 * GET:  Redirects to Angular login page with OAuth2 params stored in session.
 * POST: Validates credentials, generates auth code, redirects to redirect_uri.
 */
class OAuthAuthorizeController
{
    public function __construct(
        private readonly AuthCodeRepository $authCodeRepository,
        private readonly LoggerInterface $logger,
        private readonly OAuth2Settings $oAuth2Settings,
        private readonly RouteService $routeService,
        private readonly UtilisateurRepository $utilisateurRepository
    ) {
    }

    /**
     * @param array<string, mixed> $args
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        return match ($request->getMethod()) {
            'GET' => $this->handleGet($request, $response),
            'POST' => $this->handlePost($request, $response),
            default => throw new HttpBadRequestException($request, 'méthode non supportée'),
        };
    }

    private function handleGet(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params = $request->getQueryParams();
        $this->validateOAuthParams($request, $params);

        // Redirect to Angular login page, passing OAuth2 params as query params
        $loginUrl = '/connexion?' . http_build_query([
            'oauth' => '1',
            'client_id' => $params['client_id'],
            'redirect_uri' => $params['redirect_uri'],
            'state' => $params['state'] ?? '',
            'response_type' => $params['response_type'],
        ]);

        return $response
            ->withHeader('Location', $loginUrl)
            ->withStatus(302);
    }

    private function handlePost(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $this->routeService->getParsedRequestBody($request, ['identifiant', 'mdp', 'client_id', 'redirect_uri', 'response_type']);
        $state = $body['state'] ?? '';

        $this->validateOAuthParams($request, $body);

        try {
            $utilisateur = $this->utilisateurRepository->readOneByIdentifiantOuEmail($body['identifiant']);
            if (!$utilisateur->verifieMdp($body['mdp'])) {
                $utilisateur->incrementeTentativesEchouees();
                $this->utilisateurRepository->update($utilisateur);
                throw new UtilisateurInconnuException();
            }

            // Success: reset failed attempts counter
            if ($utilisateur->getTentativesEchouees() > 0) {
                $utilisateur->reinitialiserTentativesEchouees();
                $this->utilisateurRepository->update($utilisateur);
            }

            // Create auth code with 60 second expiry
            $result = $this->authCodeRepository->create($utilisateur->getId(), 60);

            $this->logger->info("OAuth2 auth code créé pour utilisateur {$utilisateur->getId()} ({$utilisateur->getNom()})");

            // Redirect back to redirect_uri with code and state
            $redirectUri = $body['redirect_uri'];
            $separator = str_contains($redirectUri, '?') ? '&' : '?';
            $redirectUrl = $redirectUri . $separator . http_build_query([
                'code' => $result['code'],
                'state' => $state,
            ]);

            return $response
                ->withHeader('Location', $redirectUrl)
                ->withStatus(302);
        } catch (UtilisateurInconnuException) {
            // Redirect back to login form with error — user stays in SPA flow
            $loginUrl = '/connexion?' . http_build_query([
                'oauth' => '1',
                'erreur' => 'Identifiant ou mot de passe incorrect',
                'client_id' => $body['client_id'],
                'redirect_uri' => $body['redirect_uri'],
                'state' => $state,
                'response_type' => $body['response_type'],
            ]);

            return $response
                ->withHeader('Location', $loginUrl)
                ->withStatus(302);
        }
    }

    /**
     * Validate required OAuth2 authorization request parameters.
     *
     * @param array<string, mixed> $params
     */
    private function validateOAuthParams(ServerRequestInterface $request, array $params): void
    {
        if (!isset($params['client_id']) || $params['client_id'] === '') {
            throw new HttpBadRequestException($request, "champ 'client_id' manquant");
        }
        if ($params['client_id'] !== $this->oAuth2Settings->clientId) {
            throw new HttpBadRequestException($request, 'client_id invalide');
        }
        if (!isset($params['redirect_uri']) || $params['redirect_uri'] === '') {
            throw new HttpBadRequestException($request, "champ 'redirect_uri' manquant");
        }
        if (!isset($params['response_type']) || $params['response_type'] !== 'code') {
            throw new HttpBadRequestException($request, "champ 'response_type' manquant ou invalide (doit être 'code')");
        }

        // Validate redirect_uri path (open redirect protection)
        // TEMPORARY: Path-based validation works across dev environments (localhost, Docker).
        // Combined with client_secret validation on /oauth/token, this prevents auth code theft.
        $redirectPath = parse_url((string) $params['redirect_uri'], PHP_URL_PATH);
        $allowedPath = parse_url($this->oAuth2Settings->redirectUri, PHP_URL_PATH);
        if ($redirectPath !== $allowedPath) {
            throw new HttpBadRequestException($request, 'redirect_uri non autorisé');
        }
    }
}
