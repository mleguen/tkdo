<?php

declare(strict_types=1);

namespace Test\Int;

class OAuthAuthorizeControllerTest extends IntTestCase
{
    private const AUTHORIZE_PATH = '/oauth/authorize';

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testGetRedirectsToLoginPage(bool $curl): void
    {
        // GET /oauth/authorize with valid params should redirect to /connexion
        $baseUri = getenv('TKDO_BASE_URI');
        $params = http_build_query([
            'response_type' => 'code',
            'client_id' => 'tkdo',
            'redirect_uri' => 'http://localhost:4200/auth/callback',
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
            self::AUTHORIZE_PATH . '?response_type=code&redirect_uri=http://localhost/callback',
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
            self::AUTHORIZE_PATH . '?client_id=tkdo&redirect_uri=http://localhost/callback',
            $statusCode,
            $body
        );

        $this->assertEquals(400, $statusCode);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testPostValidCredentialsRedirectsWithCode(bool $curl): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        $baseUri = getenv('TKDO_BASE_URI');
        $redirectUri = 'http://localhost:4200/auth/callback';

        $client = new \GuzzleHttp\Client(['allow_redirects' => false]);
        $response = $client->request(
            'POST',
            $baseUri . self::AUTHORIZE_PATH,
            [
                'form_params' => [
                    'identifiant' => $utilisateur->getIdentifiant(),
                    'mdp' => $utilisateur->getMdpClair(),
                    'client_id' => 'tkdo',
                    'redirect_uri' => $redirectUri,
                    'response_type' => 'code',
                    'state' => 'csrf-state-abc',
                ],
                'http_errors' => false,
            ]
        );

        $this->assertEquals(302, $response->getStatusCode());
        $location = $response->getHeaderLine('Location');
        $this->assertStringStartsWith($redirectUri, $location);
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
    public function testPostInvalidCredentialsReturns400(bool $curl): void
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
                'mdp' => 'mauvais' . $utilisateur->getMdpClair(),
                'client_id' => 'tkdo',
                'redirect_uri' => 'http://localhost/callback',
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
}
