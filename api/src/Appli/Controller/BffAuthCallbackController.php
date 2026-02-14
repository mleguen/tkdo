<?php

// PERMANENT: Stays when switching to external IdP

declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\ModelAdaptor\AuthAdaptor;
use App\Appli\Service\AuthService;
use App\Appli\Service\BffAuthService;
use App\Appli\Service\RouteService;
use App\Dom\Exception\UtilisateurInconnuException;
use App\Dom\Repository\UtilisateurRepository;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpUnauthorizedException;

/**
 * PERMANENT: BFF authentication callback.
 * Receives authorization code from frontend, exchanges it via back-channel
 * using league/oauth2-client, creates application JWT, sets HttpOnly cookie.
 */
class BffAuthCallbackController
{
    use CookieConfigTrait;

    public function __construct(
        private readonly AuthService $authService,
        private readonly BffAuthService $bffAuthService,
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
        $body = $this->routeService->getParsedRequestBody($request, ['code']);
        $codeClair = $body['code'];

        try {
            // Exchange auth code for access token via back-channel to /oauth/token
            $accessToken = $this->bffAuthService->echangeCode($codeClair);

            // Extract user claims from access token JWT
            $claims = $this->bffAuthService->extraitInfoUtilisateur($accessToken);

            // Load the user to get full user info for the response
            $utilisateur = $this->utilisateurRepository->read($claims['sub']);

            // Create application JWT and set HttpOnly cookie
            $auth = AuthAdaptor::fromUtilisateur($utilisateur, $claims['groupe_ids']);
            $jwt = $this->authService->encode($auth);

            $this->logger->info("BFF: utilisateur {$utilisateur->getId()} ({$utilisateur->getNom()}) connecté via OAuth2 callback");

            // Set HttpOnly cookie with JWT
            $response = $this->addCookieHeader($response, $jwt);

            // Return user info (not the JWT)
            return $this->routeService->getResponseWithJsonBody($response, json_encode([
                'utilisateur' => [
                    'id' => $utilisateur->getId(),
                    'nom' => $utilisateur->getNom(),
                    'email' => $utilisateur->getEmail(),
                    'genre' => $utilisateur->getGenre(),
                    'admin' => $utilisateur->getAdmin(),
                    'groupe_ids' => $claims['groupe_ids'],
                ],
            ], JSON_THROW_ON_ERROR));
        } catch (IdentityProviderException $e) {
            $this->logger->warning("BFF: échec échange code OAuth2: {$e->getMessage()}");
            throw new HttpUnauthorizedException($request, 'code invalide ou expiré');
        } catch (UtilisateurInconnuException) {
            throw new HttpUnauthorizedException($request, 'code invalide ou expiré');
        }
    }

    private function addCookieHeader(ResponseInterface $response, string $jwt): ResponseInterface
    {
        $expires = time() + $this->authService->getValidite();
        $expiresGmt = gmdate('D, d M Y H:i:s', $expires) . ' GMT';

        $cookie = sprintf(
            'tkdo_jwt=%s; Expires=%s; Path=%s; %sHttpOnly; SameSite=Strict',
            $jwt,
            $expiresGmt,
            $this->getCookiePath(),
            $this->getSecureFlag()
        );

        return $response->withHeader('Set-Cookie', $cookie);
    }
}
