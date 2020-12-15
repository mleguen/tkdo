<?php

declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\Service\RouteService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthController
{
    protected $auth;
    protected $routeService;

    public function __construct(
        RouteService $routeService
    ) {
        $this->routeService = $routeService;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $this->auth = $this->routeService->getAuth($request);
        return $response;
    }
}
