<?php

// PERMANENT: Stays when switching to external IdP

declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\ModelAdaptor\AuthAdaptor;
use App\Appli\Service\AuthService;
use App\Appli\Service\BffAuthService;
use App\Appli\Service\RouteService;
use App\Dom\Exception\UtilisateurInconnuException;
use App\Dom\Model\Appartenance;
use App\Dom\Repository\GroupeRepository;
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
 *
 * Group memberships are queried directly from the database here (not from IdP claims),
 * because group membership is an application concern that persists when switching IdP.
 */
class BffAuthCallbackController
{
    use CookieConfigTrait;

    public function __construct(
        private readonly AuthService $authService,
        private readonly BffAuthService $bffAuthService,
        private readonly GroupeRepository $groupeRepository,
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
        $seSouvenir = ($body['se_souvenir'] ?? false) === true;

        try {
            // Exchange auth code for access token via back-channel to /oauth/token
            $accessToken = $this->bffAuthService->echangeCode($codeClair);

            // Extract user identity from access token (via userinfo endpoint)
            $claims = $this->bffAuthService->extraitInfoUtilisateur($accessToken);

            // Load the user to get full user info for the response
            $utilisateur = $this->utilisateurRepository->read($claims['sub']);

            // Query group memberships directly from database (application concern, not IdP)
            $appartenances = $this->groupeRepository->readAppartenancesForUtilisateur($utilisateur->getId());
            // array_values() prevents non-sequential keys from JSON object serialization instead of array
            $groupeIds = array_values(array_map(fn(Appartenance $a) => $a->getGroupe()->getId(), $appartenances));
            $groupeAdminIds = array_values(array_map(
                fn(Appartenance $a) => $a->getGroupe()->getId(),
                array_filter($appartenances, fn(Appartenance $a) => $a->getEstAdmin())
            ));

            // Warn if user belongs to many groups — JWT cookie may exceed browser 4KB limit
            $groupCount = count($groupeIds);
            if ($groupCount > 50) {
                $this->logger->warning("Utilisateur {$utilisateur->getId()} appartient à {$groupCount} groupes — risque de dépassement de la taille JWT cookie (4KB)");
            }

            // Determine JWT/cookie validity based on "remember me"
            $validite = $seSouvenir
                ? $this->authService->getValiditeSeSouvenir()
                : $this->authService->getValidite();

            // Create application JWT and set HttpOnly cookie
            $auth = AuthAdaptor::fromUtilisateur($utilisateur, $groupeIds, $groupeAdminIds);
            $jwt = $this->authService->encode($auth, $validite);

            $this->logger->debug("BFF: utilisateur {$utilisateur->getId()} ({$utilisateur->getNom()}) connecté via OAuth2 callback");

            // Set HttpOnly cookie with JWT
            $response = $this->addCookieHeader($response, $jwt, $validite);

            // Return user info (not the JWT)
            return $this->routeService->getResponseWithJsonBody($response, json_encode([
                'utilisateur' => [
                    'id' => $utilisateur->getId(),
                    'nom' => $utilisateur->getNom(),
                    'email' => $utilisateur->getEmail(),
                    'genre' => $utilisateur->getGenre(),
                    'admin' => $utilisateur->getAdmin(),
                    'groupe_ids' => $groupeIds,
                    'groupe_admin_ids' => $groupeAdminIds,
                ],
            ], JSON_THROW_ON_ERROR));
        } catch (IdentityProviderException $e) {
            $this->logger->warning("BFF: échec échange code OAuth2: {$e->getMessage()}");
            throw new HttpUnauthorizedException($request, 'code invalide ou expiré');
        } catch (\RuntimeException $e) {
            $this->logger->warning("BFF: erreur extraction info utilisateur: {$e->getMessage()}");
            throw new HttpUnauthorizedException($request, 'code invalide ou expiré');
        } catch (UtilisateurInconnuException) {
            throw new HttpUnauthorizedException($request, 'code invalide ou expiré');
        }
    }

    private function addCookieHeader(ResponseInterface $response, string $jwt, int $validite): ResponseInterface
    {
        $expires = time() + $validite;
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
