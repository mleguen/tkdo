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
        $baseUri = getenv('TKDO_BASE_URI');
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

        $baseUri = getenv('TKDO_BASE_URI');

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

        $baseUri = getenv('TKDO_BASE_URI');

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

    public function testPostLoginWithEmailSucceeds(): void
    {
        $utilisateur = $this->utilisateur()
            ->withIdentifiant('emailuser')
            ->withEmail('emailuser@test.com')
            ->persist(self::$em);

        $baseUri = getenv('TKDO_BASE_URI');
        $client = new \GuzzleHttp\Client(['allow_redirects' => false]);
        $response = $client->request(
            'POST',
            $baseUri . self::AUTHORIZE_PATH,
            [
                'form_params' => [
                    'identifiant' => 'emailuser@test.com', // email instead of username
                    'mdp' => $utilisateur->getMdpClair(),
                    'client_id' => 'tkdo',
                    'redirect_uri' => self::VALID_REDIRECT_URI,
                    'response_type' => 'code',
                    'state' => 'test',
                ],
                'http_errors' => false,
            ]
        );

        $this->assertEquals(302, $response->getStatusCode());
        $location = $response->getHeaderLine('Location');
        $this->assertStringStartsWith(self::VALID_REDIRECT_URI, $location);
        $this->assertStringContainsString('code=', $location);

        // Verify no DB side effects: tentatives_echouees remains 0 after successful email login
        self::$em->clear();
        $reloaded = self::$em->find(\App\Appli\ModelAdaptor\UtilisateurAdaptor::class, $utilisateur->getId());
        $this->assertNotNull($reloaded);
        $this->assertEquals(0, $reloaded->getTentativesEchouees(), 'tentatives_echouees should remain 0 after successful email login');
    }

    public function testPostInvalidCredentialsReturnsStandardizedErrorMessage(): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('testmsg')->persist(self::$em);

        $baseUri = getenv('TKDO_BASE_URI');
        $client = new \GuzzleHttp\Client(['allow_redirects' => false]);
        $response = $client->request(
            'POST',
            $baseUri . self::AUTHORIZE_PATH,
            [
                'form_params' => [
                    'identifiant' => $utilisateur->getIdentifiant(),
                    'mdp' => 'wrong-password',
                    'client_id' => 'tkdo',
                    'redirect_uri' => self::VALID_REDIRECT_URI,
                    'response_type' => 'code',
                    'state' => 'test',
                ],
                'http_errors' => false,
            ]
        );

        $this->assertEquals(302, $response->getStatusCode());
        $location = $response->getHeaderLine('Location');
        // Verify standardized error message (URL-encoded)
        $this->assertStringContainsString(
            'erreur=' . urlencode('Identifiant ou mot de passe incorrect'),
            $location
        );
    }

    public function testPostUnknownUserReturnsStandardizedErrorMessage(): void
    {
        $baseUri = getenv('TKDO_BASE_URI');
        $client = new \GuzzleHttp\Client(['allow_redirects' => false]);
        $response = $client->request(
            'POST',
            $baseUri . self::AUTHORIZE_PATH,
            [
                'form_params' => [
                    'identifiant' => 'nonexistent-user',
                    'mdp' => 'anypassword',
                    'client_id' => 'tkdo',
                    'redirect_uri' => self::VALID_REDIRECT_URI,
                    'response_type' => 'code',
                    'state' => 'test',
                ],
                'http_errors' => false,
            ]
        );

        $this->assertEquals(302, $response->getStatusCode());
        $location = $response->getHeaderLine('Location');
        // Same error message for unknown user (no info leak)
        $this->assertStringContainsString(
            'erreur=' . urlencode('Identifiant ou mot de passe incorrect'),
            $location
        );
    }

    public function testFailedLoginIncrementsTentativesEchouees(): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('failcount')->persist(self::$em);
        $this->assertEquals(0, $utilisateur->getTentativesEchouees());

        $baseUri = getenv('TKDO_BASE_URI');
        $client = new \GuzzleHttp\Client(['allow_redirects' => false]);

        // First failed attempt
        $client->request('POST', $baseUri . self::AUTHORIZE_PATH, [
            'form_params' => [
                'identifiant' => $utilisateur->getIdentifiant(),
                'mdp' => 'wrong-password',
                'client_id' => 'tkdo',
                'redirect_uri' => self::VALID_REDIRECT_URI,
                'response_type' => 'code',
                'state' => 'test',
            ],
            'http_errors' => false,
        ]);

        // Reload from DB to check counter
        self::$em->clear();
        $reloaded = self::$em->find(\App\Appli\ModelAdaptor\UtilisateurAdaptor::class, $utilisateur->getId());
        $this->assertNotNull($reloaded);
        $this->assertEquals(1, $reloaded->getTentativesEchouees());

        // Second failed attempt
        $client->request('POST', $baseUri . self::AUTHORIZE_PATH, [
            'form_params' => [
                'identifiant' => $utilisateur->getIdentifiant(),
                'mdp' => 'still-wrong',
                'client_id' => 'tkdo',
                'redirect_uri' => self::VALID_REDIRECT_URI,
                'response_type' => 'code',
                'state' => 'test',
            ],
            'http_errors' => false,
        ]);

        self::$em->clear();
        $reloaded = self::$em->find(\App\Appli\ModelAdaptor\UtilisateurAdaptor::class, $utilisateur->getId());
        $this->assertNotNull($reloaded);
        $this->assertEquals(2, $reloaded->getTentativesEchouees());
    }

    public function testPostLoginWithSharedEmailReturnsErrorNotServerError(): void
    {
        $sharedEmail = 'famille@test.com';
        // Create two users with the same email (families sharing emails)
        $parent1 = $this->utilisateur()
            ->withIdentifiant('parent1')
            ->withEmail($sharedEmail)
            ->persist(self::$em);
        $parent2 = $this->utilisateur()
            ->withIdentifiant('parent2')
            ->withEmail($sharedEmail)
            ->persist(self::$em);

        $baseUri = getenv('TKDO_BASE_URI');
        $client = new \GuzzleHttp\Client(['allow_redirects' => false]);
        $response = $client->request(
            'POST',
            $baseUri . self::AUTHORIZE_PATH,
            [
                'form_params' => [
                    'identifiant' => $sharedEmail,
                    'mdp' => 'mdpparent1',
                    'client_id' => 'tkdo',
                    'redirect_uri' => self::VALID_REDIRECT_URI,
                    'response_type' => 'code',
                    'state' => 'test',
                ],
                'http_errors' => false,
            ]
        );

        // Should get a redirect with error message, NOT a 500 server error
        $this->assertEquals(302, $response->getStatusCode());
        $location = $response->getHeaderLine('Location');
        $this->assertStringContainsString(
            'erreur=' . urlencode('Identifiant ou mot de passe incorrect'),
            $location
        );

        // Verify no DB side effects: neither user's counter was incremented
        self::$em->clear();
        $reloadedParent1 = self::$em->find(\App\Appli\ModelAdaptor\UtilisateurAdaptor::class, $parent1->getId());
        $reloadedParent2 = self::$em->find(\App\Appli\ModelAdaptor\UtilisateurAdaptor::class, $parent2->getId());
        $this->assertNotNull($reloadedParent1);
        $this->assertNotNull($reloadedParent2);
        $this->assertEquals(0, $reloadedParent1->getTentativesEchouees(), 'parent1 tentatives_echouees should remain 0');
        $this->assertEquals(0, $reloadedParent2->getTentativesEchouees(), 'parent2 tentatives_echouees should remain 0');
    }

    public function testPostLoginWithNonExistentUserProducesZeroDbWrites(): void
    {
        // Create a user as baseline to verify no collateral DB writes
        $existingUser = $this->utilisateur()->withIdentifiant('existinguser')->persist(self::$em);
        $this->assertEquals(0, $existingUser->getTentativesEchouees());

        $baseUri = getenv('TKDO_BASE_URI');
        $client = new \GuzzleHttp\Client(['allow_redirects' => false]);
        $response = $client->request('POST', $baseUri . self::AUTHORIZE_PATH, [
            'form_params' => [
                'identifiant' => 'nonexistent-user-xyz',
                'mdp' => 'anypassword',
                'client_id' => 'tkdo',
                'redirect_uri' => self::VALID_REDIRECT_URI,
                'response_type' => 'code',
                'state' => 'test',
            ],
            'http_errors' => false,
        ]);

        // Should redirect with error, not 500
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString(
            'erreur=' . urlencode('Identifiant ou mot de passe incorrect'),
            $response->getHeaderLine('Location')
        );

        // Verify zero DB writes: existing user's counter untouched
        self::$em->clear();
        $reloaded = self::$em->find(\App\Appli\ModelAdaptor\UtilisateurAdaptor::class, $existingUser->getId());
        $this->assertNotNull($reloaded);
        $this->assertEquals(0, $reloaded->getTentativesEchouees());
    }

    public function testSuccessfulLoginResetsTentativesEchouees(): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('resetcount')->persist(self::$em);

        $baseUri = getenv('TKDO_BASE_URI');
        $client = new \GuzzleHttp\Client(['allow_redirects' => false]);

        // First: fail once to increment counter
        $client->request('POST', $baseUri . self::AUTHORIZE_PATH, [
            'form_params' => [
                'identifiant' => $utilisateur->getIdentifiant(),
                'mdp' => 'wrong-password',
                'client_id' => 'tkdo',
                'redirect_uri' => self::VALID_REDIRECT_URI,
                'response_type' => 'code',
                'state' => 'test',
            ],
            'http_errors' => false,
        ]);

        self::$em->clear();
        $reloaded = self::$em->find(\App\Appli\ModelAdaptor\UtilisateurAdaptor::class, $utilisateur->getId());
        $this->assertEquals(1, $reloaded->getTentativesEchouees());

        // Then: login successfully to reset
        $response = $client->request('POST', $baseUri . self::AUTHORIZE_PATH, [
            'form_params' => [
                'identifiant' => $utilisateur->getIdentifiant(),
                'mdp' => $utilisateur->getMdpClair(),
                'client_id' => 'tkdo',
                'redirect_uri' => self::VALID_REDIRECT_URI,
                'response_type' => 'code',
                'state' => 'test',
            ],
            'http_errors' => false,
        ]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringContainsString('code=', $response->getHeaderLine('Location'));

        self::$em->clear();
        $reloaded = self::$em->find(\App\Appli\ModelAdaptor\UtilisateurAdaptor::class, $utilisateur->getId());
        $this->assertEquals(0, $reloaded->getTentativesEchouees());
    }
}
