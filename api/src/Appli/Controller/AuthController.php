<?php

declare(strict_types=1);

namespace App\Appli\Controller;

use App\Appli\ModelAdaptor\AuthAdaptor;
use App\Appli\Service\RouteService;
use App\Dom\Model\Auth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthController
{
    protected ?AuthAdaptor $auth = null;
    protected RouteService $routeService;

    public function __construct(
        RouteService $routeService
    ) {
        $this->routeService = $routeService;
    }

    /**
     * @param array<string, mixed> $args
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $this->auth = $this->routeService->getAuth($request);
        return $response;
    }

    /**
     * Returns the authenticated user. Must be called after parent::__invoke().
     * @return Auth
     */
    protected function getAuth(): Auth
    {
        assert($this->auth !== null);
        return $this->auth;
    }
}
