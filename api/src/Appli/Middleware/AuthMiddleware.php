<?php

declare(strict_types=1);

namespace App\Appli\Middleware;

use App\Appli\Exception\AuthPasDeTokenException;
use App\Appli\Exception\AuthTokenInvalideException;
use App\Appli\ModelAdaptor\AuthAdaptor;
use App\Appli\Service\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class AuthMiddleware implements MiddlewareInterface
{
    private const COOKIE_NAME = 'tkdo_jwt';

    public function __construct(private readonly LoggerInterface $logger, private readonly AuthService $authService)
    {
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $serverParams = $request->getServerParams();
        $cookies = $request->getCookieParams();

        try {
            // Try cookie FIRST (new flow), then fall back to Authorization header (backward compatibility)
            $auth = $this->authentifieViaCookie($cookies)
                ?? $this->authentifieViaHeader($serverParams['HTTP_AUTHORIZATION'] ?? '');

            $this->logger->info("Utilisateur {$auth->getIdUtilisateur()} authentifié" . ($auth->estAdmin() ? ' (admin)' : ''));
            return $handler->handle($request->withAttribute('auth', $auth));
        }
        catch (AuthPasDeTokenException | AuthTokenInvalideException $err) {
            return $handler->handle($request->withAttribute('authErr', $err));
        }
    }

    /**
     * Authentifie l'expéditeur d'une requête par son cookie JWT
     *
     * @param array<string, string> $cookies
     * @throws AuthTokenInvalideException
     */
    private function authentifieViaCookie(array $cookies): ?AuthAdaptor
    {
        if (!isset($cookies[self::COOKIE_NAME])) {
            return null;
        }

        return $this->authService->decode($cookies[self::COOKIE_NAME]);
    }

    /**
     * Authentifie l'expéditeur d'une requête par son 'authorization' header
     *
     * @throws AuthTokenInvalideException
     * @throws AuthPasDeTokenException
     */
    private function authentifieViaHeader(string $authorizationHeader): AuthAdaptor
    {
        if (
            preg_match('/^Bearer (.+)$/', $authorizationHeader, $matches) ||
            // Le token peut également fourni comme utilisateur (sans mot de passe)
            // d'une authentification basique (par exemple avec `curl -u $token:`)
            (preg_match('/^Basic ([^:]+)$/', $authorizationHeader, $matches) &&
                preg_match('/^([^:]+):$/', base64_decode($matches[1]), $matches))
        ) {
            return $this->authService->decode($matches[1]);
        }

        throw new AuthPasDeTokenException();
    }
}
