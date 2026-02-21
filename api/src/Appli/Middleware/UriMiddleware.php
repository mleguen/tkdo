<?php

declare(strict_types=1);

namespace App\Appli\Middleware;

use App\Appli\Service\UriService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UriMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly UriService $uriService)
    {
    }

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->uriService->setBaseUriFromRequest(
            $request->getUri(),
            $request->getHeaderLine('X-Forwarded-Proto')
        );

        return $handler->handle($request);
    }
}
