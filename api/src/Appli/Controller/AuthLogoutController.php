<?php

declare(strict_types=1);

namespace App\Appli\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthLogoutController
{
    /**
     * @param array<string, mixed> $args
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        // Clear the JWT cookie by setting Max-Age=0
        $response = $this->addClearCookieHeader($response);

        return $response->withStatus(204);
    }

    private function addClearCookieHeader(ResponseInterface $response): ResponseInterface
    {
        // In production, use Secure flag; in dev mode, skip it for HTTP testing
        $devModeEnv = getenv('TKDO_DEV_MODE');
        $devMode = $devModeEnv !== false ? boolval($devModeEnv) : true;
        $secureFlag = $devMode ? '' : 'Secure; ';

        // Cookie path: Use /api in production (behind nginx), / in dev (direct API access)
        $apiBasePathEnv = getenv('TKDO_API_BASE_PATH');
        $apiBasePath = $apiBasePathEnv !== false ? $apiBasePathEnv : '/';

        // Build cookie string that clears the cookie (Max-Age=0)
        $cookie = sprintf(
            'tkdo_jwt=; Max-Age=0; Path=%s; %sHttpOnly; SameSite=Strict',
            $apiBasePath,
            $secureFlag
        );

        return $response->withHeader('Set-Cookie', $cookie);
    }
}
