<?php
declare(strict_types=1);

namespace App\Application\Middleware;

use App\Application\Service\TokenService;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface as Middleware;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpUnauthorizedException;

// TODO: à remplacer par Tuupola\Middleware\JwtAuthentication

class AuthMiddleware implements Middleware
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var TokenService
     */
    protected $tokenService;

    public function __construct(LoggerInterface $logger, TokenService $tokenService)
    {
        $this->logger = $logger;
        $this->tokenService = $tokenService;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        if ($request->getRequestTarget() !== '/connexion') {
            try {
                $serverParams = $request->getServerParams();
    
                if (
                    !isset($serverParams['HTTP_AUTHORIZATION']) ||
                    (strpos($serverParams['HTTP_AUTHORIZATION'], "Bearer ") !== 0)
                ) {
                    throw new Exception('Token absent !');
                }
                $token = substr($serverParams['HTTP_AUTHORIZATION'], strlen("Bearer "));

                $request = $request->withAttribute('idUtilisateurAuth', $this->tokenService->decode($token));
            } catch (Exception $e) {
                $this->logger->warning($e->getMessage());
                throw new HttpUnauthorizedException($request);
            }
        }
        
        return $handler->handle($request);
    }
}
