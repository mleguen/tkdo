<?php
declare(strict_types=1);

namespace App\Application\Middleware;

use App\Application\Service\AuthPasDeTokenException;
use App\Application\Service\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;

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
        try {
            $auth = $this->authService->authentifie($serverParams['HTTP_AUTHORIZATION'] ?? '');
            $this->logger->info("Utilisateur {$auth['idUtilisateurAuth']} authentifié" . ($auth['estAdmin'] ? ' (admin)' : ''));
            $request = $request
                ->withAttribute('idUtilisateurAuth', $auth['idUtilisateurAuth'])
                ->withAttribute('estAdmin', $auth['estAdmin']);
        }
        catch (AuthPasDeTokenException $e) {
            $this->logger->info($e->getMessage());
        }
        catch (\Exception $e) {
            $this->logger->warning($e->getMessage());
        }
        
        return $handler->handle($request);
    }
}
