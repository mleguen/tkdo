<?php

declare(strict_types=1);

namespace Test\Int;

use GuzzleHttp\Cookie\CookieJar;

class AuthCookieIntTest extends IntTestCase
{
    /**
     * Test that cookie-based authentication works for protected endpoints
     */
    public function testCookieAuthWorksForProtectedEndpoints(): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        // Step 1: Login and get auth code
        $this->requestApi(
            false,
            'POST',
            '/auth/login',
            $loginStatusCode,
            $loginBody,
            '',
            [
                'identifiant' => $utilisateur->getIdentifiant(),
                'mdp' => $utilisateur->getMdpClair(),
            ]
        );
        $this->assertEquals(200, $loginStatusCode);
        $code = $loginBody['code'];

        // Step 2: Exchange code for cookie (using Guzzle with cookie jar)
        $cookieJar = new CookieJar();
        $client = new \GuzzleHttp\Client(['cookies' => $cookieJar]);

        $tokenResponse = $client->request(
            'POST',
            getenv('TKDO_BASE_URI') . '/auth/token',
            [
                'json' => ['code' => $code],
                'http_errors' => false,
            ]
        );

        $this->assertEquals(200, $tokenResponse->getStatusCode());

        // Verify cookie was set
        $cookie = $cookieJar->getCookieByName('tkdo_jwt');
        $this->assertNotNull($cookie, 'Cookie tkdo_jwt should be set');
        // Path is configurable via TKDO_API_BASE_PATH; defaults to /
        $this->assertNotEmpty($cookie->getPath());
        // Secure flag is only set in production mode (TKDO_DEV_MODE=0)
        // In dev mode (tests), Secure is omitted to allow HTTP testing
        $this->assertTrue($cookie->getHttpOnly());

        // Step 3: Use cookie to access a protected endpoint
        $userResponse = $client->request(
            'GET',
            getenv('TKDO_BASE_URI') . '/utilisateur/' . $utilisateur->getId(),
            [
                'http_errors' => false,
            ]
        );

        $this->assertEquals(200, $userResponse->getStatusCode());
        $userData = json_decode((string)$userResponse->getBody(), true);
        $this->assertEquals($utilisateur->getId(), $userData['id']);
        $this->assertEquals($utilisateur->getNom(), $userData['nom']);
    }

    /**
     * Test that protected endpoints return 401 without cookie or Bearer token
     */
    public function testProtectedEndpointReturns401WithoutAuth(): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        // Access protected endpoint without any authentication
        $client = new \GuzzleHttp\Client();
        $response = $client->request(
            'GET',
            getenv('TKDO_BASE_URI') . '/utilisateur/' . $utilisateur->getId(),
            [
                'http_errors' => false,
            ]
        );

        $this->assertEquals(401, $response->getStatusCode());
    }

    /**
     * Test that Bearer token still works (backward compatibility)
     */
    public function testBearerTokenStillWorks(): void
    {
        // Use the existing postConnexion which uses Bearer token
        $utilisateur = $this->postConnexion(false);

        // Access protected endpoint with Bearer token (via token set by postConnexion)
        $this->requestApi(
            false,
            'GET',
            '/utilisateur/' . $utilisateur->getId(),
            $statusCode,
            $body
        );

        $this->assertEquals(200, $statusCode);
        $this->assertEquals($utilisateur->getId(), $body['id']);
    }

    /**
     * Test that logout clears the cookie
     */
    public function testLogoutClearsCookie(): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        // Step 1: Login and get auth code
        $this->requestApi(
            false,
            'POST',
            '/auth/login',
            $loginStatusCode,
            $loginBody,
            '',
            [
                'identifiant' => $utilisateur->getIdentifiant(),
                'mdp' => $utilisateur->getMdpClair(),
            ]
        );
        $this->assertEquals(200, $loginStatusCode);
        $code = $loginBody['code'];

        // Step 2: Exchange code for cookie
        $cookieJar = new CookieJar();
        $client = new \GuzzleHttp\Client(['cookies' => $cookieJar]);

        $tokenResponse = $client->request(
            'POST',
            getenv('TKDO_BASE_URI') . '/auth/token',
            [
                'json' => ['code' => $code],
                'http_errors' => false,
            ]
        );
        $this->assertEquals(200, $tokenResponse->getStatusCode());

        // Verify cookie exists
        $cookie = $cookieJar->getCookieByName('tkdo_jwt');
        $this->assertNotNull($cookie, 'Cookie should exist before logout');

        // Step 3: Logout
        $logoutResponse = $client->request(
            'POST',
            getenv('TKDO_BASE_URI') . '/auth/logout',
            [
                'http_errors' => false,
            ]
        );
        $this->assertEquals(204, $logoutResponse->getStatusCode());

        // Verify cookie is cleared (Max-Age=0 sets empty value)
        $cookie = $cookieJar->getCookieByName('tkdo_jwt');
        // Cookie may still exist in jar but should be empty/expired
        if ($cookie !== null) {
            $this->assertEmpty($cookie->getValue(), 'Cookie value should be empty after logout');
        }

        // Step 4: Verify protected endpoint returns 401 after logout
        $userResponse = $client->request(
            'GET',
            getenv('TKDO_BASE_URI') . '/utilisateur/' . $utilisateur->getId(),
            [
                'http_errors' => false,
            ]
        );
        $this->assertEquals(401, $userResponse->getStatusCode());
    }
}
