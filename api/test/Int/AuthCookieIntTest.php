<?php

declare(strict_types=1);

namespace Test\Int;

use GuzzleHttp\Cookie\CookieJar;

class AuthCookieIntTest extends IntTestCase
{
    private const AUTHORIZE_PATH = '/oauth/authorize';
    private const CALLBACK_PATH = '/auth/callback';

    /**
     * Helper: create a user and get an auth code via the OAuth authorize endpoint.
     *
     * @return array{code: string, utilisateur: \App\Appli\ModelAdaptor\UtilisateurAdaptor}
     */
    private function createUserAndGetCode(): array
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        $baseUri = getenv('TKDO_API_BASE_URI');
        $client = new \GuzzleHttp\Client(['allow_redirects' => false]);

        $response = $client->request(
            'POST',
            $baseUri . self::AUTHORIZE_PATH,
            [
                'form_params' => [
                    'identifiant' => $utilisateur->getIdentifiant(),
                    'mdp' => $utilisateur->getMdpClair(),
                    'client_id' => 'tkdo',
                    'redirect_uri' => 'http://localhost:4200/auth/callback',
                    'response_type' => 'code',
                    'state' => 'test',
                ],
                'http_errors' => false,
            ]
        );

        $this->assertEquals(302, $response->getStatusCode());
        $location = $response->getHeaderLine('Location');
        parse_str(parse_url($location, PHP_URL_QUERY) ?: '', $queryParams);

        return ['code' => $queryParams['code'], 'utilisateur' => $utilisateur];
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
            getenv('TKDO_API_BASE_URI') . '/utilisateur/' . $utilisateur->getId(),
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
        ['code' => $code, 'utilisateur' => $utilisateur] = $this->createUserAndGetCode();

        // Exchange code via BFF callback
        $cookieJar = new CookieJar();
        $client = new \GuzzleHttp\Client(['cookies' => $cookieJar]);

        $callbackResponse = $client->request(
            'POST',
            getenv('TKDO_API_BASE_URI') . self::CALLBACK_PATH,
            [
                'json' => ['code' => $code],
                'http_errors' => false,
            ]
        );
        $this->assertEquals(200, $callbackResponse->getStatusCode());

        // Verify cookie exists
        $cookie = $cookieJar->getCookieByName('tkdo_jwt');
        $this->assertNotNull($cookie, 'Cookie should exist before logout');

        // Logout
        $logoutResponse = $client->request(
            'POST',
            getenv('TKDO_API_BASE_URI') . '/auth/logout',
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

        // Verify protected endpoint returns 401 after logout
        $userResponse = $client->request(
            'GET',
            getenv('TKDO_API_BASE_URI') . '/utilisateur/' . $utilisateur->getId(),
            [
                'http_errors' => false,
            ]
        );
        $this->assertEquals(401, $userResponse->getStatusCode());
    }
}
