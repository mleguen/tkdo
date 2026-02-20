<?php

declare(strict_types=1);

namespace Test\Int;

use GuzzleHttp\Cookie\CookieJar;
use Test\Builder\GroupeBuilder;

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
            getenv('TKDO_BASE_URI') . self::CALLBACK_PATH,
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
        $this->assertEquals([], $body['utilisateur']['groupe_admin_ids']);

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
            getenv('TKDO_BASE_URI') . self::CALLBACK_PATH,
            [
                'json' => ['code' => $code],
                'http_errors' => false,
            ]
        );
        $this->assertEquals(200, $response->getStatusCode());

        // Use cookie to access a protected endpoint
        $userResponse = $client->request(
            'GET',
            getenv('TKDO_BASE_URI') . '/utilisateur/' . $utilisateur->getId(),
            ['http_errors' => false]
        );

        $this->assertEquals(200, $userResponse->getStatusCode());
        $userData = json_decode((string) $userResponse->getBody(), true);
        $this->assertEquals($utilisateur->getId(), $userData['id']);
    }

    public function testValidCodeWithGroupsReturnsGroupeIdsAndAdminIds(): void
    {
        ['code' => $code, 'utilisateur' => $utilisateur] = $this->createUserAndGetCode();

        // Create groups with memberships
        $groupe1 = GroupeBuilder::unGroupe()
            ->withNom('Famille')
            ->withAppartenance($utilisateur, false)
            ->persist(self::$em);

        $groupe2 = GroupeBuilder::unGroupe()
            ->withNom('Amis')
            ->withAppartenance($utilisateur, true) // admin
            ->persist(self::$em);

        // Exchange code via BFF callback
        $cookieJar = new CookieJar();
        $client = new \GuzzleHttp\Client(['cookies' => $cookieJar]);

        $response = $client->request(
            'POST',
            getenv('TKDO_BASE_URI') . self::CALLBACK_PATH,
            [
                'json' => ['code' => $code],
                'http_errors' => false,
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);

        // Verify response contains correct group claims
        $groupeIds = $body['utilisateur']['groupe_ids'];
        $groupeAdminIds = $body['utilisateur']['groupe_admin_ids'];

        $this->assertContains($groupe1->getId(), $groupeIds);
        $this->assertContains($groupe2->getId(), $groupeIds);
        $this->assertCount(2, $groupeIds);

        $this->assertContains($groupe2->getId(), $groupeAdminIds);
        $this->assertNotContains($groupe1->getId(), $groupeAdminIds);
        $this->assertCount(1, $groupeAdminIds);
    }

    /**
     * AC #2: Session refresh reflects new group membership.
     * Login with no groups, add group, re-login, verify group in claims.
     */
    public function testSessionRefreshReflectsNewGroupMembership(): void
    {
        // First login — no groups
        ['code' => $code1, 'utilisateur' => $utilisateur] = $this->createUserAndGetCode();

        $cookieJar1 = new CookieJar();
        $client1 = new \GuzzleHttp\Client(['cookies' => $cookieJar1]);

        $response1 = $client1->request(
            'POST',
            getenv('TKDO_BASE_URI') . self::CALLBACK_PATH,
            [
                'json' => ['code' => $code1],
                'http_errors' => false,
            ]
        );

        $this->assertEquals(200, $response1->getStatusCode());
        $body1 = json_decode((string) $response1->getBody(), true);
        $this->assertEquals([], $body1['utilisateur']['groupe_ids']);
        $this->assertEquals([], $body1['utilisateur']['groupe_admin_ids']);

        // Add user to a group
        $groupe = GroupeBuilder::unGroupe()
            ->withNom('Nouveau Groupe')
            ->withAppartenance($utilisateur, true)
            ->persist(self::$em);

        // Second login — get new auth code and exchange it
        $baseUri = getenv('TKDO_BASE_URI');
        $client2 = new \GuzzleHttp\Client(['allow_redirects' => false]);

        $authResponse = $client2->request(
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

        $this->assertEquals(302, $authResponse->getStatusCode());
        $location = $authResponse->getHeaderLine('Location');
        parse_str(parse_url($location, PHP_URL_QUERY) ?: '', $queryParams);
        $code2 = $queryParams['code'];

        $cookieJar2 = new CookieJar();
        $client3 = new \GuzzleHttp\Client(['cookies' => $cookieJar2]);

        $response2 = $client3->request(
            'POST',
            $baseUri . self::CALLBACK_PATH,
            [
                'json' => ['code' => $code2],
                'http_errors' => false,
            ]
        );

        $this->assertEquals(200, $response2->getStatusCode());
        $body2 = json_decode((string) $response2->getBody(), true);

        // Verify the new group appears in claims
        $this->assertContains($groupe->getId(), $body2['utilisateur']['groupe_ids']);
        $this->assertContains($groupe->getId(), $body2['utilisateur']['groupe_admin_ids']);
    }

    public function testValidCodeWithArchivedGroupExcludesFromClaims(): void
    {
        ['code' => $code, 'utilisateur' => $utilisateur] = $this->createUserAndGetCode();

        $activeGroupe = GroupeBuilder::unGroupe()
            ->withNom('Active')
            ->withAppartenance($utilisateur, false)
            ->persist(self::$em);

        GroupeBuilder::unGroupe()
            ->withNom('Archived')
            ->withArchive(true)
            ->withAppartenance($utilisateur, true)
            ->persist(self::$em);

        $cookieJar = new CookieJar();
        $client = new \GuzzleHttp\Client(['cookies' => $cookieJar]);

        $response = $client->request(
            'POST',
            getenv('TKDO_BASE_URI') . self::CALLBACK_PATH,
            [
                'json' => ['code' => $code],
                'http_errors' => false,
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);

        $this->assertCount(1, $body['utilisateur']['groupe_ids']);
        $this->assertContains($activeGroupe->getId(), $body['utilisateur']['groupe_ids']);
        $this->assertEquals([], $body['utilisateur']['groupe_admin_ids']);
    }

    public function testValidCodeWithNonConsecutiveAdminIndexesReturnsSequentialArray(): void
    {
        ['code' => $code, 'utilisateur' => $utilisateur] = $this->createUserAndGetCode();

        // Create 3 groups: user is admin of groups 1 and 3 (non-consecutive after array_filter)
        GroupeBuilder::unGroupe()
            ->withNom('G1')
            ->withAppartenance($utilisateur, true)
            ->persist(self::$em);

        GroupeBuilder::unGroupe()
            ->withNom('G2')
            ->withAppartenance($utilisateur, false) // not admin
            ->persist(self::$em);

        GroupeBuilder::unGroupe()
            ->withNom('G3')
            ->withAppartenance($utilisateur, true)
            ->persist(self::$em);

        $cookieJar = new CookieJar();
        $client = new \GuzzleHttp\Client(['cookies' => $cookieJar]);

        $response = $client->request(
            'POST',
            getenv('TKDO_BASE_URI') . self::CALLBACK_PATH,
            [
                'json' => ['code' => $code],
                'http_errors' => false,
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);

        $this->assertCount(3, $body['utilisateur']['groupe_ids']);
        $this->assertCount(2, $body['utilisateur']['groupe_admin_ids']);

        // Verify the arrays are sequential (important for JSON serialization)
        $adminIds = $body['utilisateur']['groupe_admin_ids'];
        $this->assertEquals(array_values($adminIds), $adminIds);
    }

    public function testRememberMeTrueSetsExtendedCookieExpiry(): void
    {
        ['code' => $code] = $this->createUserAndGetCode();

        $cookieJar = new CookieJar();
        $client = new \GuzzleHttp\Client(['cookies' => $cookieJar]);

        $response = $client->request(
            'POST',
            getenv('TKDO_BASE_URI') . self::CALLBACK_PATH,
            [
                'json' => ['code' => $code, 'se_souvenir' => true],
                'http_errors' => false,
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());

        $cookie = $cookieJar->getCookieByName('tkdo_jwt');
        $this->assertNotNull($cookie, 'Cookie tkdo_jwt should be set');

        // Cookie Expires should be ~7 days from now (604800 seconds)
        $expires = $cookie->getExpires();
        $this->assertNotNull($expires);
        $expectedMin = time() + 604800 - 30; // 30s tolerance
        $expectedMax = time() + 604800 + 30;
        $this->assertGreaterThanOrEqual($expectedMin, $expires);
        $this->assertLessThanOrEqual($expectedMax, $expires);
    }

    public function testNoRememberMeSetsDefaultCookieExpiry(): void
    {
        ['code' => $code] = $this->createUserAndGetCode();

        $cookieJar = new CookieJar();
        $client = new \GuzzleHttp\Client(['cookies' => $cookieJar]);

        $response = $client->request(
            'POST',
            getenv('TKDO_BASE_URI') . self::CALLBACK_PATH,
            [
                'json' => ['code' => $code], // no se_souvenir
                'http_errors' => false,
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());

        $cookie = $cookieJar->getCookieByName('tkdo_jwt');
        $this->assertNotNull($cookie, 'Cookie tkdo_jwt should be set');

        // Cookie Expires should be ~1 hour from now (3600 seconds)
        $expires = $cookie->getExpires();
        $this->assertNotNull($expires);
        $expectedMin = time() + 3600 - 30; // 30s tolerance
        $expectedMax = time() + 3600 + 30;
        $this->assertGreaterThanOrEqual($expectedMin, $expires);
        $this->assertLessThanOrEqual($expectedMax, $expires);
    }

    public function testRememberMeFalseExplicitlySetsDefaultCookieExpiry(): void
    {
        ['code' => $code] = $this->createUserAndGetCode();

        $cookieJar = new CookieJar();
        $client = new \GuzzleHttp\Client(['cookies' => $cookieJar]);

        $response = $client->request(
            'POST',
            getenv('TKDO_BASE_URI') . self::CALLBACK_PATH,
            [
                'json' => ['code' => $code, 'se_souvenir' => false],
                'http_errors' => false,
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());

        $cookie = $cookieJar->getCookieByName('tkdo_jwt');
        $this->assertNotNull($cookie, 'Cookie tkdo_jwt should be set');

        // Same as no se_souvenir: ~1 hour
        $expires = $cookie->getExpires();
        $this->assertNotNull($expires);
        $expectedMin = time() + 3600 - 30;
        $expectedMax = time() + 3600 + 30;
        $this->assertGreaterThanOrEqual($expectedMin, $expires);
        $this->assertLessThanOrEqual($expectedMax, $expires);
    }

    /**
     * Verify that truthy non-boolean values for se_souvenir do NOT trigger remember-me.
     * Protects the strict `=== true` comparison from future regression.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideTruthyNonBooleanValues')]
    public function testTruthyNonBooleanSeSouvenirDoesNotTriggerRememberMe(mixed $truthyValue): void
    {
        ['code' => $code] = $this->createUserAndGetCode();

        $cookieJar = new CookieJar();
        $client = new \GuzzleHttp\Client(['cookies' => $cookieJar]);

        $response = $client->request(
            'POST',
            getenv('TKDO_BASE_URI') . self::CALLBACK_PATH,
            [
                'json' => ['code' => $code, 'se_souvenir' => $truthyValue],
                'http_errors' => false,
            ]
        );

        $this->assertEquals(200, $response->getStatusCode());

        $cookie = $cookieJar->getCookieByName('tkdo_jwt');
        $this->assertNotNull($cookie, 'Cookie tkdo_jwt should be set');

        // Should get default ~1 hour expiry, NOT extended 7-day expiry
        $expires = $cookie->getExpires();
        $this->assertNotNull($expires);
        $expectedMin = time() + 3600 - 30;
        $expectedMax = time() + 3600 + 30;
        $this->assertGreaterThanOrEqual($expectedMin, $expires);
        $this->assertLessThanOrEqual($expectedMax, $expires);
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function provideTruthyNonBooleanValues(): array
    {
        return [
            'string "true"' => ['true'],
            'integer 1' => [1],
        ];
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
