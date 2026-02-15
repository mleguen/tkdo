<?php

declare(strict_types=1);

namespace Test\Int;

class OAuthAuthorizeControllerTest extends IntTestCase
{
    private const AUTHORIZE_PATH = '/oauth/authorize';
    private const VALID_REDIRECT_URI = 'http://localhost:4200/auth/callback';

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testGetRedirectsToLoginPage(bool $curl): void
    {
        // GET /oauth/authorize with valid params should redirect to /connexion
        $baseUri = getenv('TKDO_API_BASE_URI');
        $params = http_build_query([
            'response_type' => 'code',
            'client_id' => 'tkdo',
            'redirect_uri' => self::VALID_REDIRECT_URI,
            'state' => 'test-state-123',
        ]);

        $client = new \GuzzleHttp\Client(['allow_redirects' => false]);
        $response = $client->request(
            'GET',
            $baseUri . self::AUTHORIZE_PATH . '?' . $params,
            ['http_errors' => false]
        );

        $this->assertEquals(302, $response->getStatusCode());
        $location = $response->getHeaderLine('Location');
        $this->assertStringContainsString('/connexion', $location);
        $this->assertStringContainsString('oauth=1', $location);
        $this->assertStringContainsString('client_id=tkdo', $location);
        $this->assertStringContainsString('state=test-state-123', $location);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testGetMissingClientIdReturns400(bool $curl): void
    {
        $this->requestApi(
            $curl,
            'GET',
            self::AUTHORIZE_PATH . '?response_type=code&redirect_uri=' . urlencode(self::VALID_REDIRECT_URI),
            $statusCode,
            $body
        );

        $this->assertEquals(400, $statusCode);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testGetMissingResponseTypeReturns400(bool $curl): void
    {
        $this->requestApi(
            $curl,
            'GET',
            self::AUTHORIZE_PATH . '?client_id=tkdo&redirect_uri=' . urlencode(self::VALID_REDIRECT_URI),
            $statusCode,
            $body
        );

        $this->assertEquals(400, $statusCode);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testGetInvalidRedirectUriReturns400(bool $curl): void
    {
        $this->requestApi(
            $curl,
            'GET',
            self::AUTHORIZE_PATH . '?response_type=code&client_id=tkdo&redirect_uri=' . urlencode('http://evil.com/steal'),
            $statusCode,
            $body
        );

        $this->assertEquals(400, $statusCode);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testPostValidCredentialsRedirectsWithCode(bool $curl): void
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
                    'redirect_uri' => self::VALID_REDIRECT_URI,
                    'response_type' => 'code',
                    'state' => 'csrf-state-abc',
                ],
                'http_errors' => false,
            ]
        );

        $this->assertEquals(302, $response->getStatusCode());
        $location = $response->getHeaderLine('Location');
        $this->assertStringStartsWith(self::VALID_REDIRECT_URI, $location);
        $this->assertStringContainsString('code=', $location);
        $this->assertStringContainsString('state=csrf-state-abc', $location);

        // Extract the code from the redirect URL
        parse_str(parse_url($location, PHP_URL_QUERY) ?: '', $queryParams);
        $this->assertArrayHasKey('code', $queryParams);
        // Code should be 64 chars hex (32 bytes)
        $this->assertEquals(64, strlen($queryParams['code']));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $queryParams['code']);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testPostInvalidCredentialsRedirectsToLoginWithError(bool $curl): void
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
                    'mdp' => 'mauvais' . $utilisateur->getMdpClair(),
                    'client_id' => 'tkdo',
                    'redirect_uri' => self::VALID_REDIRECT_URI,
                    'response_type' => 'code',
                    'state' => 'test',
                ],
                'http_errors' => false,
            ]
        );

        // Invalid credentials should redirect back to login form with error
        $this->assertEquals(302, $response->getStatusCode());
        $location = $response->getHeaderLine('Location');
        $this->assertStringContainsString('/connexion', $location);
        $this->assertStringContainsString('erreur=', $location);
        $this->assertStringContainsString('oauth=1', $location);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testPostInvalidRedirectUriReturns400(bool $curl): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        $this->requestApi(
            $curl,
            'POST',
            self::AUTHORIZE_PATH,
            $statusCode,
            $body,
            '',
            [
                'identifiant' => $utilisateur->getIdentifiant(),
                'mdp' => $utilisateur->getMdpClair(),
                'client_id' => 'tkdo',
                'redirect_uri' => 'http://evil.com/steal',
                'response_type' => 'code',
                'state' => 'test',
            ]
        );

        $this->assertEquals(400, $statusCode);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testPostMissingFieldsReturns400(bool $curl): void
    {
        $this->requestApi(
            $curl,
            'POST',
            self::AUTHORIZE_PATH,
            $statusCode,
            $body,
            '',
            [
                'identifiant' => 'test',
                'mdp' => 'test',
                // missing client_id, redirect_uri, response_type
            ]
        );

        $this->assertEquals(400, $statusCode);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testInvalidClientIdReturns400(bool $curl): void
    {
        $this->requestApi(
            $curl,
            'GET',
            self::AUTHORIZE_PATH . '?response_type=code&client_id=wrong-client&redirect_uri=' . urlencode(self::VALID_REDIRECT_URI),
            $statusCode,
            $body
        );

        $this->assertEquals(400, $statusCode);
    }
}
