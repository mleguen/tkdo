<?php
declare(strict_types=1);

namespace Tests\Application\Actions;

use Psr\Http\Message\ResponseInterface;
use Tests\TestCase;

abstract class ActionTestCase extends TestCase
{
    protected function handleRequest(
        string $method,
        string $path,
        $query = '',
        $body = null
    ): ResponseInterface {
        $request = $this->createRequest($method, $path, $query, $body);
        return $this->app->handle($request);
    }

    protected function handleRequestWithAuthHeader(
        string $authHeader,
        string $method,
        string $path,
        $query = '',
        $body = null
    ): ResponseInterface {
        $request = $this->createRequest(
            $method,
            $path,
            $query,
            $body,
            ['HTTP_AUTHORIZATION' => $authHeader]
        );
        return $this->app->handle($request);
    }

    protected function handleAuthRequest(
        int $idUtilisateurAuth,
        bool $estAdmin,
        string $method,
        string $path,
        $query = '',
        $body = null
    ): ResponseInterface {
        return $this->handleRequestWithAuthHeader(
            "Bearer " . $this->authService->encodeAuthToken($idUtilisateurAuth, $estAdmin),
            $method, $path, $query, $body
        );
    }
}
