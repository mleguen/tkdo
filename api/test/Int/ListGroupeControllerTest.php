<?php

declare(strict_types=1);

namespace Test\Int;

use GuzzleHttp\Cookie\CookieJar;
use Test\Builder\GroupeBuilder;

class ListGroupeControllerTest extends IntTestCase
{
    private const AUTHORIZE_PATH = '/oauth/authorize';
    private const CALLBACK_PATH = '/auth/callback';
    private const GROUPE_PATH = '/groupe';

    /**
     * Helper: create a user, optionally with groups, and authenticate via BFF OAuth flow.
     * Returns a Guzzle client with cookie-based JWT auth.
     *
     * @return array{client: \GuzzleHttp\Client, utilisateur: \App\Appli\ModelAdaptor\UtilisateurAdaptor}
     */
    private function authenticateUser(?\App\Appli\ModelAdaptor\UtilisateurAdaptor $utilisateur = null): array
    {
        if ($utilisateur === null) {
            $utilisateur = $this->utilisateur()->withIdentifiant('testuser')->persist(self::$em);
        }

        $baseUri = getenv('TKDO_BASE_URI');
        $authClient = new \GuzzleHttp\Client(['allow_redirects' => false]);

        $response = $authClient->request(
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

        $cookieJar = new CookieJar();
        $client = new \GuzzleHttp\Client(['cookies' => $cookieJar]);

        $callbackResponse = $client->request(
            'POST',
            $baseUri . self::CALLBACK_PATH,
            [
                'json' => ['code' => $queryParams['code']],
                'http_errors' => false,
            ]
        );
        $this->assertEquals(200, $callbackResponse->getStatusCode());

        return ['client' => $client, 'utilisateur' => $utilisateur];
    }

    public function testListGroupeReturnsActiveAndArchivedGroups(): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('testuser')->persist(self::$em);

        GroupeBuilder::unGroupe()
            ->withNom('Famille')
            ->withAppartenance($utilisateur, false)
            ->persist(self::$em);
        GroupeBuilder::unGroupe()
            ->withNom('Amis')
            ->withAppartenance($utilisateur, false)
            ->persist(self::$em);
        GroupeBuilder::unGroupe()
            ->withNom('Noël 2024')
            ->withArchive(true)
            ->withAppartenance($utilisateur, false)
            ->persist(self::$em);
        GroupeBuilder::unGroupe()
            // Use ASCII-safe name ('A' < 'N' in all collations, unlike 'É' which varies by collation)
            ->withNom('Automne 2023')
            ->withArchive(true)
            ->withAppartenance($utilisateur, false)
            ->persist(self::$em);

        ['client' => $client] = $this->authenticateUser($utilisateur);

        $response = $client->request(
            'GET',
            getenv('TKDO_BASE_URI') . self::GROUPE_PATH,
            ['http_errors' => false]
        );

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);

        $this->assertCount(2, $body['actifs']);
        $this->assertCount(2, $body['archives']);

        // Verify alphabetical sort order (orderBy g.nom ASC)
        $this->assertEquals('Amis', $body['actifs'][0]['nom']);
        $this->assertEquals('Famille', $body['actifs'][1]['nom']);

        // Verify archived groups are also sorted alphabetically
        $this->assertEquals('Automne 2023', $body['archives'][0]['nom']);
        $this->assertEquals('Noël 2024', $body['archives'][1]['nom']);
        $this->assertTrue($body['archives'][0]['archive']);
        $this->assertTrue($body['archives'][1]['archive']);
    }

    public function testListGroupeWithNoGroupsReturnsEmptyArrays(): void
    {
        ['client' => $client] = $this->authenticateUser();

        $response = $client->request(
            'GET',
            getenv('TKDO_BASE_URI') . self::GROUPE_PATH,
            ['http_errors' => false]
        );

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);

        $this->assertCount(0, $body['actifs']);
        $this->assertCount(0, $body['archives']);
    }

    public function testListGroupeIncludesEstAdminFlag(): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('testuser')->persist(self::$em);

        GroupeBuilder::unGroupe()
            ->withNom('Admin Group')
            ->withAppartenance($utilisateur, true)
            ->persist(self::$em);
        GroupeBuilder::unGroupe()
            ->withNom('Member Group')
            ->withAppartenance($utilisateur, false)
            ->persist(self::$em);

        // Auth via BFF so JWT includes groupe_admin_ids
        ['client' => $client] = $this->authenticateUser($utilisateur);

        $response = $client->request(
            'GET',
            getenv('TKDO_BASE_URI') . self::GROUPE_PATH,
            ['http_errors' => false]
        );

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);

        $this->assertCount(2, $body['actifs']);

        // Verify alphabetical sort order and estAdmin flags by position
        $this->assertEquals('Admin Group', $body['actifs'][0]['nom']);
        $this->assertTrue($body['actifs'][0]['estAdmin']);
        $this->assertEquals('Member Group', $body['actifs'][1]['nom']);
        $this->assertFalse($body['actifs'][1]['estAdmin']);
    }

    public function testListGroupeWithOnlyActiveGroups(): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('testuser')->persist(self::$em);

        GroupeBuilder::unGroupe()
            ->withNom('Famille')
            ->withAppartenance($utilisateur, false)
            ->persist(self::$em);
        GroupeBuilder::unGroupe()
            ->withNom('Amis')
            ->withAppartenance($utilisateur, true)
            ->persist(self::$em);

        ['client' => $client] = $this->authenticateUser($utilisateur);

        $response = $client->request(
            'GET',
            getenv('TKDO_BASE_URI') . self::GROUPE_PATH,
            ['http_errors' => false]
        );

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);

        $this->assertCount(2, $body['actifs']);
        $this->assertCount(0, $body['archives']);

        // Verify alphabetical sort order (orderBy g.nom ASC)
        $this->assertEquals('Amis', $body['actifs'][0]['nom']);
        $this->assertEquals('Famille', $body['actifs'][1]['nom']);

        // Verify estAdmin flags (Amis=true admin, Famille=false member)
        $this->assertTrue($body['actifs'][0]['estAdmin']);
        $this->assertFalse($body['actifs'][1]['estAdmin']);
    }

    /**
     * Verifies that archived groups always have estAdmin=false, even when the user
     * is admin of that group. This is because JWT groupe_admin_ids only includes
     * active-group admin IDs (readAppartenancesForUtilisateur filters archive=false),
     * so archived groups are never in the admin list.
     */
    public function testListGroupeArchivedGroupsAlwaysHaveEstAdminFalse(): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('testuser')->persist(self::$em);

        // User is admin of this archived group, but estAdmin should still be false
        // because JWT only carries active-group admin IDs
        GroupeBuilder::unGroupe()
            ->withNom('Old Project')
            ->withArchive(true)
            ->withAppartenance($utilisateur, true) // admin=true in DB
            ->persist(self::$em);

        // Active group where user is admin (for comparison)
        GroupeBuilder::unGroupe()
            ->withNom('Current Team')
            ->withAppartenance($utilisateur, true)
            ->persist(self::$em);

        ['client' => $client] = $this->authenticateUser($utilisateur);

        $response = $client->request(
            'GET',
            getenv('TKDO_BASE_URI') . self::GROUPE_PATH,
            ['http_errors' => false]
        );

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);

        $this->assertCount(1, $body['actifs']);
        $this->assertCount(1, $body['archives']);

        // Active group: estAdmin=true (admin ID present in JWT)
        $this->assertEquals('Current Team', $body['actifs'][0]['nom']);
        $this->assertTrue($body['actifs'][0]['estAdmin']);

        // Archived group: estAdmin=false (admin ID NOT in JWT, even though DB says admin)
        $this->assertEquals('Old Project', $body['archives'][0]['nom']);
        $this->assertFalse($body['archives'][0]['estAdmin']);
    }

    public function testListGroupeRequiresAuthentication(): void
    {
        $this->requestApi(
            false,
            'GET',
            self::GROUPE_PATH,
            $statusCode,
            $body
        );

        $this->assertEquals(401, $statusCode);
    }
}
