<?php
declare(strict_types=1);

namespace App\Appli\Middleware;

use App\Appli\Exception\AuthPasDeTokenException;
use App\Appli\Exception\AuthTokenInvalideException;
use App\Appli\ModelAdaptor\AuthAdaptor;
use App\Appli\Service\AuthService;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpUnauthorizedException;

class AuthMiddleware implements MiddlewareInterface
{
    private $logger;
    private $authService;

    public function __construct(
        LoggerInterface $logger,
        AuthService $authService
    )
    {
        $this->logger = $logger;
        $this->authService = $authService;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $serverParams = $request->getServerParams();
        try {
            $auth = $this->authentifie($serverParams['HTTP_AUTHORIZATION'] ?? '');
            $this->logger->info("Utilisateur {$auth->getIdUtilisateur()} authentifié" . ($auth->estAdmin() ? ' (admin)' : ''));
            return $handler->handle($request->withAttribute('auth', $auth));
        }
        catch (AuthPasDeTokenException | AuthTokenInvalideException $err) {
            return $handler->handle($request->withAttribute('authErr', $err));
        }
        
    }

    /**
     * Authentifie l'expéditeur d'une requête par son 'authorization' header
     * 
     * @throws AuthTokenInvalideException
     * @throws AuthPasDeTokenException
     */
    private function authentifie(string $authorizationHeader): AuthAdaptor
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
