<?php

declare(strict_types=1);

namespace Test\Int;

use GuzzleHttp\Cookie\CookieJar;

class BffAuthCallbackControllerTest extends IntTestCase
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

    public function testValidCodeReturnsUserPayloadAndSetsCookie(): void
    {
        ['code' => $code, 'utilisateur' => $utilisateur] = $this->createUserAndGetCode();

        // Exchange code via BFF callback with cookie jar
        $cookieJar = new CookieJar();
        $client = new \GuzzleHttp\Client(['cookies' => $cookieJar]);

        $response = $client->request(
            'POST',
            getenv('TKDO_API_BASE_URI') . self::CALLBACK_PATH,
            [
                'json' => ['code' => $code],
                'http_errors' => false,
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);

        // Verify response contains user payload
        $this->assertArrayHasKey('utilisateur', $body);
        $this->assertEquals($utilisateur->getId(), $body['utilisateur']['id']);
        $this->assertEquals($utilisateur->getNom(), $body['utilisateur']['nom']);
        $this->assertEquals($utilisateur->getEmail(), $body['utilisateur']['email']);
        $this->assertEquals($utilisateur->getGenre(), $body['utilisateur']['genre']);
        $this->assertEquals($utilisateur->getAdmin(), $body['utilisateur']['admin']);
        $this->assertEquals([], $body['utilisateur']['groupe_ids']);

        // Should NOT return the token in the body
        $this->assertArrayNotHasKey('token', $body);
        $this->assertArrayNotHasKey('access_token', $body);

        // Verify cookie was set
        $cookie = $cookieJar->getCookieByName('tkdo_jwt');
        $this->assertNotNull($cookie, 'Cookie tkdo_jwt should be set');
        $this->assertTrue($cookie->getHttpOnly());
    }

    public function testInvalidCodeReturns401(): void
    {
        $this->requestApi(
            false,
            'POST',
            self::CALLBACK_PATH,
            $statusCode,
            $body,
            '',
            ['code' => 'totally_invalid_code']
        );

        $this->assertEquals(401, $statusCode);
    }

    public function testMissingCodeReturns400(): void
    {
        $this->requestApi(
            false,
            'POST',
            self::CALLBACK_PATH,
            $statusCode,
            $body,
            '',
            []
        );

        $this->assertEquals(400, $statusCode);
    }

    public function testCookieAuthWorksAfterCallback(): void
    {
        ['code' => $code, 'utilisateur' => $utilisateur] = $this->createUserAndGetCode();

        // Exchange code via BFF callback
        $cookieJar = new CookieJar();
        $client = new \GuzzleHttp\Client(['cookies' => $cookieJar]);

        $response = $client->request(
            'POST',
            getenv('TKDO_API_BASE_URI') . self::CALLBACK_PATH,
            [
                'json' => ['code' => $code],
                'http_errors' => false,
            ]
        );
        $this->assertEquals(200, $response->getStatusCode());

        // Use cookie to access a protected endpoint
        $userResponse = $client->request(
            'GET',
            getenv('TKDO_API_BASE_URI') . '/utilisateur/' . $utilisateur->getId(),
            ['http_errors' => false]
        );

        $this->assertEquals(200, $userResponse->getStatusCode());
        $userData = json_decode((string) $userResponse->getBody(), true);
        $this->assertEquals($utilisateur->getId(), $userData['id']);
    }

    /**
     * Race condition test for concurrent code exchange is covered by
     * OAuthTokenControllerTest::testConcurrentCodeExchangeOnlyOneSucceeds.
     *
     * A concurrent BFF callback test cannot run here because the BFF makes a
     * back-channel call to /oauth/token on the same PHP-FPM pool, causing a
     * deadlock when all workers are occupied by callback requests.
     * In production, the BFF calls an external IdP, so this scenario cannot occur.
     */
}
