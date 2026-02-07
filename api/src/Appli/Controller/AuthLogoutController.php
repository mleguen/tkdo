<?php

declare(strict_types=1);

namespace App\Appli\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthLogoutController
{
    use CookieConfigTrait;

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
        $cookie = sprintf(
            'tkdo_jwt=; Max-Age=0; Path=%s; %sHttpOnly; SameSite=Strict',
            $this->getCookiePath(),
            $this->getSecureFlag()
        );

        return $response->withHeader('Set-Cookie', $cookie);
    }
}
