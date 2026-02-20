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

        ['client' => $client] = $this->authenticateUser($utilisateur);

        $response = $client->request(
            'GET',
            getenv('TKDO_BASE_URI') . self::GROUPE_PATH,
            ['http_errors' => false]
        );

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);

        $this->assertCount(2, $body['actifs']);
        $this->assertCount(1, $body['archives']);

        // Verify alphabetical sort order (orderBy g.nom ASC)
        $this->assertEquals('Amis', $body['actifs'][0]['nom']);
        $this->assertEquals('Famille', $body['actifs'][1]['nom']);

        $this->assertEquals('Noël 2024', $body['archives'][0]['nom']);
        $this->assertTrue($body['archives'][0]['archive']);
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

        $adminFlags = [];
        foreach ($body['actifs'] as $groupe) {
            $adminFlags[$groupe['nom']] = $groupe['estAdmin'];
        }
        $this->assertTrue($adminFlags['Admin Group']);
        $this->assertFalse($adminFlags['Member Group']);
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
