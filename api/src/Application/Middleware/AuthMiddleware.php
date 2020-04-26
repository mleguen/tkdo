<?php
declare(strict_types=1);

namespace App\Application\Middleware;

use App\Application\Mock\MockData;
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
     * @var MockData
     */
    protected $mock;

    /**
     * @param LoggerInterface $logger
     * @param MockData  $mock
     */
    public function __construct(LoggerInterface $logger, MockData $mock)
    {
        $this->logger = $logger;
        $this->mock = $mock;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        if (($request->getRequestTarget() !== '/api/connexion') && (
            !isset($_SERVER['HTTP_AUTHORIZATION']) ||
            ($_SERVER['HTTP_AUTHORIZATION'] !== "Bearer ".$this->mock->getToken())
        )) {
            throw new HttpUnauthorizedException($request);
        }

        return $handler->handle($request);
    }
}
