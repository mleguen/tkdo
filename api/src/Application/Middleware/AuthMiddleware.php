<?php
declare(strict_types=1);

namespace App\Application\Middleware;

use App\Application\Service\AuthService;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;

// TODO: à remplacer par Tuupola\Middleware\JwtAuthentication

class AuthMiddleware implements Middleware
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var AuthService
     */
    protected $authService;

    public function __construct(LoggerInterface $logger, AuthService $authService)
    {
        $this->logger = $logger;
        $this->authService = $authService;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $serverParams = $request->getServerParams();
        if (
            !isset($serverParams['HTTP_AUTHORIZATION']) ||
            (strpos($serverParams['HTTP_AUTHORIZATION'], "Bearer ") !== 0)
        ) {
            $this->logger->info('Bearer token absent');
        }
        else {
            $token = substr($serverParams['HTTP_AUTHORIZATION'], strlen("Bearer "));
            try {
                $idUtilisateurAuth = $this->authService->decode($token);
                $this->logger->info("Utilisateur $idUtilisateurAuth authentifié");
                $request = $request->withAttribute('idUtilisateurAuth', $idUtilisateurAuth);
            } catch (Exception $e) {
                $this->logger->warning($e->getMessage());
            }
        }
        
        return $handler->handle($request);
    }
}
