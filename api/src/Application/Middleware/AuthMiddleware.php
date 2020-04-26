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

class AuthMiddleware implements Middleware
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        // if (($request->getRequestTarget() !== '/api/connexion') &&
        //     (!isset($_SERVER['HTTP_AUTHORIZATION']) ||
        //     ($_SERVER['HTTP_AUTHORIZATION'] !== "Bearer ".MockData::token))) {
        if ($request->getRequestTarget() !== '/api/connexion') {
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $this->logger->info($_SERVER['HTTP_AUTHORIZATION']);
            }

            if (!isset($_SERVER['HTTP_AUTHORIZATION']) ||
                ($_SERVER['HTTP_AUTHORIZATION'] !== "Bearer ".MockData::token)) {
                throw new HttpUnauthorizedException($request);
            }
        }

        return $handler->handle($request);
    }
}
