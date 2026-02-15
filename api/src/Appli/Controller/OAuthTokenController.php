<?php

// TEMPORARY: Will be replaced by external IdP

declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\ModelAdaptor\AuthAdaptor;
use App\Appli\Service\AuthService;
use App\Appli\Service\RouteService;
use App\Appli\Settings\OAuth2Settings;
use App\Dom\Exception\UtilisateurInconnuException;
use App\Dom\Repository\AuthCodeRepository;
use App\Dom\Repository\UtilisateurRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

/**
 * TEMPORARY: OAuth2 Authorization Server — token endpoint.
 * Will be replaced by external IdP (Google, Auth0, etc.) post-MVP.
 *
 * POST: Accepts grant_type=authorization_code, returns standard OAuth2 token response.
 *       The access_token is a JWT containing user claims (sub, nom, email, genre, admin, groupe_ids).
 */
class OAuthTokenController
{
    public function __construct(
        private readonly AuthCodeRepository $authCodeRepository,
        private readonly AuthService $authService,
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
        $body = $this->routeService->getParsedRequestBody($request, ['grant_type', 'code', 'client_id']);

        if ($body['grant_type'] !== 'authorization_code') {
            return $this->oauthError($response, 400, 'unsupported_grant_type', "grant_type non supporté (doit être 'authorization_code')");
        }

        // Validate client_id matches configured value
        if ($body['client_id'] !== $this->oAuth2Settings->clientId) {
            return $this->oauthError($response, 401, 'invalid_client', 'client_id invalide');
        }

        // Validate client_secret (prevents auth code theft)
        $clientSecret = $body['client_secret'] ?? '';
        if ($clientSecret !== $this->oAuth2Settings->clientSecret) {
            return $this->oauthError($response, 401, 'invalid_client', 'client_secret invalide');
        }

        $codeClair = $body['code'];

        // Find valid auth code by checking hash (query via repository)
        $authCode = $this->findValidAuthCode($codeClair);
        if ($authCode === null) {
            return $this->oauthError($response, 401, 'invalid_grant', 'code invalide ou expiré');
        }

        // Atomically mark as used - prevents race conditions
        $marked = $this->authCodeRepository->marqueUtilise($authCode->getId());
        if (!$marked) {
            return $this->oauthError($response, 401, 'invalid_grant', 'code invalide ou expiré');
        }

        // Load the user
        try {
            $utilisateur = $this->utilisateurRepository->read($authCode->getUtilisateurId());
        } catch (UtilisateurInconnuException) {
            return $this->oauthError($response, 401, 'invalid_grant', 'code invalide ou expiré');
        }

        // Create JWT as access_token with user claims
        // groupe_ids will be populated in Story 2.2+, empty for now
        $auth = AuthAdaptor::fromUtilisateur($utilisateur, []);
        $jwt = $this->authService->encode($auth);

        $this->logger->info("OAuth2 token émis pour utilisateur {$utilisateur->getId()} ({$utilisateur->getNom()})");

        // Return standard OAuth2 token response
        return $this->routeService->getResponseWithJsonBody($response, json_encode([
            'access_token' => $jwt,
            'token_type' => 'Bearer',
            'expires_in' => $this->authService->getValidite(),
        ], JSON_THROW_ON_ERROR));
    }

    /**
     * Return a standard OAuth2 error response (RFC 6749 §5.2).
     */
    private function oauthError(ResponseInterface $response, int $status, string $error, string $description): ResponseInterface
    {
        $response = $response->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode([
            'error' => $error,
            'error_description' => $description,
        ], JSON_THROW_ON_ERROR));
        return $response;
    }

    /**
     * Find a valid auth code matching the clear-text code.
     */
    private function findValidAuthCode(string $codeClair): ?\App\Dom\Model\AuthCode
    {
        $codes = $this->authCodeRepository->readAllValid();

        foreach ($codes as $code) {
            if ($code->verifieCode($codeClair)) {
                return $code;
            }
        }

        return null;
    }
}
