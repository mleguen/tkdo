<?php

declare(strict_types=1);

namespace Test\Int;

use DateTime;

/**
 * Comprehensive integration tests for API error responses
 *
 * Tests verify consistent error handling across all API endpoints:
 * - 400 Bad Request for invalid data and validation errors
 * - 401 Unauthorized for missing or invalid authentication
 * - 403 Forbidden for insufficient permissions
 * - 404 Not Found for missing resources
 * - Consistent JSON error response format
 */
class ErrorHandlingIntTest extends IntTestCase
{
    /**
     * Tests 401 Unauthorized responses when authentication token is missing
     *
     * All authenticated endpoints should return 401 with consistent message
     * when no authentication token is provided
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function test401TokenAbsent(bool $curl): void
    {
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');

        // Test various endpoints without authentication
        $endpoints = [
            ['method' => 'GET', 'path' => '/occasion', 'query' => ''],
            ['method' => 'POST', 'path' => '/occasion', 'data' => ['date' => '2025-01-01', 'titre' => 'test']],
            ['method' => 'GET', 'path' => "/utilisateur/{$utilisateur->getId()}", 'query' => ''],
            ['method' => 'POST', 'path' => '/idee', 'data' => ['idUtilisateur' => $utilisateur->getId(), 'description' => 'test', 'idAuteur' => $utilisateur->getId()]],
        ];

        foreach ($endpoints as $endpoint) {
            $this->requestApi(
                $curl,
                $endpoint['method'],
                $endpoint['path'],
                $statusCode,
                $body,
                $endpoint['query'] ?? '',
                $endpoint['data'] ?? null
            );

            $this->assertEquals(401, $statusCode, "Expected 401 for {$endpoint['method']} {$endpoint['path']}");
            $this->assertIsArray($body);
            $this->assertArrayHasKey('message', $body);
            $this->assertEquals("token d'authentification absent", $body['message']);
        }
    }

    /**
     * Tests 401 Unauthorized response for invalid authentication token
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function test401TokenInvalide(bool $curl): void
    {
        // Set an invalid token
        $this->token = 'invalid-token-xyz';

        $this->requestApi(
            $curl,
            'GET',
            '/occasion',
            $statusCode,
            $body
        );

        $this->assertEquals(401, $statusCode);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('message', $body);
        $this->assertStringContainsString('token', strtolower($body['message']));
    }

    /**
     * Tests 400 Bad Request responses for invalid login credentials
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function test400IdentifiantsInvalides(bool $curl): void
    {
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');

        // Test with wrong password
        $this->requestApi(
            $curl,
            'POST',
            '/connexion',
            $statusCode,
            $body,
            '',
            [
                'identifiant' => $utilisateur->getIdentifiant(),
                'mdp' => 'wrongpassword',
            ]
        );

        $this->assertEquals(400, $statusCode);
        $this->assertEquals(['message' => 'identifiants invalides'], $body);

        // Test with unknown username
        $this->requestApi(
            $curl,
            'POST',
            '/connexion',
            $statusCode,
            $body,
            '',
            [
                'identifiant' => 'unknownuser',
                'mdp' => 'anypassword',
            ]
        );

        $this->assertEquals(400, $statusCode);
        $this->assertEquals(['message' => 'identifiants invalides'], $body);
    }

    /**
     * Tests 400 Bad Request for missing required fields
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function test400ChampManquant(bool $curl): void
    {
        $this->postConnexion($curl);

        // Test missing required field in POST /occasion
        $this->requestApi(
            $curl,
            'POST',
            '/occasion',
            $statusCode,
            $body,
            '',
            ['date' => '2025-01-01'] // Missing 'titre'
        );

        $this->assertEquals(400, $statusCode);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('message', $body);
        $this->assertStringContainsString('titre', $body['message']);
        $this->assertStringContainsString('manquant', $body['message']);
    }

    /**
     * Tests 400 Bad Request for invalid JSON syntax
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function test400JsonInvalide(bool $curl): void
    {
        // Skip this test for curl mode as it's difficult to send invalid JSON with curl
        if ($curl) {
            $this->markTestSkipped('Invalid JSON test not applicable for curl mode');
        }

        $this->postConnexion($curl);

        // Send invalid JSON directly using Guzzle
        $headers = [
            'Content-type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => "Bearer {$this->token}",
        ];

        $client = new \GuzzleHttp\Client();
        $response = $client->request(
            'POST',
            getenv('TKDO_BASE_URI') . '/occasion',
            [
                'body' => '{invalid json}',
                'headers' => $headers,
                'http_errors' => false,
            ]
        );

        $statusCode = $response->getStatusCode();
        $body = json_decode((string) $response->getBody(), true);

        $this->assertEquals(400, $statusCode);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('message', $body);
        $this->assertStringContainsString('JSON', $body['message']);
    }

    /**
     * Tests 400 Bad Request for unknown user reference
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function test400UtilisateurInconnu(bool $curl): void
    {
        $auteur = $this->postConnexion($curl);
        $idUtilisateurInexistant = $auteur->getId() + 9999;

        $this->requestApi(
            $curl,
            'POST',
            '/idee',
            $statusCode,
            $body,
            '',
            [
                'idUtilisateur' => $idUtilisateurInexistant,
                'description' => 'test',
                'idAuteur' => $auteur->getId(),
            ]
        );

        $this->assertEquals(400, $statusCode);
        $this->assertEquals(['message' => 'utilisateur inconnu'], $body);
    }

    /**
     * Tests 400 Bad Request for duplicate exclusion
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function test400DoublonExclusion(bool $curl): void
    {
        $user1 = $this->creeUtilisateurEnBase('user1', ['admin' => true]);
        $user2 = $this->creeUtilisateurEnBase('user2');

        $this->postConnexion($curl, $user1);

        // Create first exclusion
        $this->requestApi(
            $curl,
            'POST',
            "/utilisateur/{$user1->getId()}/exclusion",
            $statusCode,
            $body,
            '',
            ['idQuiNeDoitPasRecevoir' => $user2->getId()]
        );
        $this->assertEquals(200, $statusCode);

        // Try to create duplicate exclusion
        $this->requestApi(
            $curl,
            'POST',
            "/utilisateur/{$user1->getId()}/exclusion",
            $statusCode,
            $body,
            '',
            ['idQuiNeDoitPasRecevoir' => $user2->getId()]
        );

        $this->assertEquals(400, $statusCode);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('message', $body);
        $this->assertStringContainsString('exclusion', strtolower($body['message']));
    }

    /**
     * Tests 400 Bad Request for already deleted idea
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function test400IdeeDejaSupprimee(bool $curl): void
    {
        $idee = $this->creeIdeeEnBase(['dateSuppression' => new DateTime()]);
        $this->postConnexion($curl, $idee->getAuteur());

        $this->requestApi(
            $curl,
            'POST',
            "/idee/{$idee->getId()}/suppression",
            $statusCode,
            $body
        );

        $this->assertEquals(400, $statusCode);
        $this->assertEquals(['message' => "l'idée a déjà été marquée comme supprimée"], $body);
    }

    /**
     * Tests 403 Forbidden when non-admin tries admin-only operation
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function test403PasAdmin(bool $curl): void
    {
        $nonAdmin = $this->creeUtilisateurEnBase('nonAdmin', ['admin' => false]);
        $this->postConnexion($curl, $nonAdmin);

        // Try to list all users (admin only)
        $this->requestApi(
            $curl,
            'GET',
            '/utilisateur',
            $statusCode,
            $body
        );

        $this->assertEquals(403, $statusCode);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('message', $body);
        $this->assertStringContainsString('administrateur', $body['message']);
    }

    /**
     * Tests 403 Forbidden when user tries to create idea for another user
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function test403PasAuteur(bool $curl): void
    {
        $auteur = $this->creeUtilisateurEnBase('auteur');
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');
        $tiers = $this->creeUtilisateurEnBase('tiers');

        $this->postConnexion($curl, $tiers);

        $this->requestApi(
            $curl,
            'POST',
            '/idee',
            $statusCode,
            $body,
            '',
            [
                'idUtilisateur' => $utilisateur->getId(),
                'description' => 'test',
                'idAuteur' => $auteur->getId(),
            ]
        );

        $this->assertEquals(403, $statusCode);
        $this->assertEquals(['message' => "l'utilisateur authentifié n'est ni l'auteur de l'idée, ni un administrateur"], $body);
    }

    /**
     * Tests 403 Forbidden when non-participant tries to access occasion
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function test403PasParticipant(bool $curl): void
    {
        $participant = $this->creeUtilisateurEnBase('participant');
        $nonParticipant = $this->creeUtilisateurEnBase('nonParticipant');
        $occasion = $this->creeOccasionEnBase(['participants' => [$participant]]);

        $this->postConnexion($curl, $nonParticipant);

        $this->requestApi(
            $curl,
            'GET',
            "/occasion/{$occasion->getId()}",
            $statusCode,
            $body
        );

        $this->assertEquals(403, $statusCode);
        $this->assertEquals(['message' => "l'utilisateur authentifié ne participe pas à l'occasion et n'est pas un administrateur"], $body);
    }

    /**
     * Tests 404 Not Found for non-existent user
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function test404UtilisateurInconnu(bool $curl): void
    {
        $connecte = $this->postConnexion($curl);
        $idInexistant = $connecte->getId() + 9999;

        $this->requestApi(
            $curl,
            'GET',
            "/utilisateur/$idInexistant",
            $statusCode,
            $body
        );

        $this->assertEquals(404, $statusCode);
        $this->assertEquals(['message' => 'utilisateur inconnu'], $body);
    }

    /**
     * Tests 404 Not Found for non-existent occasion
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function test404OccasionInconnue(bool $curl): void
    {
        $this->postConnexion($curl);
        $idInexistant = 9999;

        $this->requestApi(
            $curl,
            'GET',
            "/occasion/$idInexistant",
            $statusCode,
            $body
        );

        $this->assertEquals(404, $statusCode);
        $this->assertEquals(['message' => 'occasion inconnue'], $body);
    }

    /**
     * Tests 404 Not Found for non-existent idea
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function test404IdeeInconnue(bool $curl): void
    {
        $this->postConnexion($curl);
        $idInexistant = 9999;

        $this->requestApi(
            $curl,
            'POST',
            "/idee/$idInexistant/suppression",
            $statusCode,
            $body
        );

        $this->assertEquals(404, $statusCode);
        $this->assertEquals(['message' => 'idée inconnue'], $body);
    }

    /**
     * Tests that error response format is consistent across all error types
     *
     * All errors should return a JSON object with a 'message' field
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testFormatReponseErreurConsistant(bool $curl): void
    {
        $utilisateur = $this->creeUtilisateurEnBase('utilisateur');

        // Collect various error responses
        $errors = [];

        // 401 error
        $this->requestApi($curl, 'GET', '/occasion', $statusCode, $body);
        $errors['401'] = $body;

        // 400 error (bad credentials)
        $this->requestApi($curl, 'POST', '/connexion', $statusCode, $body, '', [
            'identifiant' => 'unknown',
            'mdp' => 'wrong'
        ]);
        $errors['400'] = $body;

        $this->postConnexion($curl, $utilisateur);

        // 403 error (not admin)
        $this->requestApi($curl, 'GET', '/utilisateur', $statusCode, $body);
        $errors['403'] = $body;

        // 404 error (not found)
        $this->requestApi($curl, 'GET', '/utilisateur/9999', $statusCode, $body);
        $errors['404'] = $body;

        // Verify all errors have consistent format
        foreach ($errors as $code => $error) {
            $this->assertIsArray($error, "Error $code should return an array");
            $this->assertArrayHasKey('message', $error, "Error $code should have a 'message' field");
            $this->assertIsString($error['message'], "Error $code message should be a string");
            $this->assertNotEmpty($error['message'], "Error $code message should not be empty");
        }
    }

    /**
     * Tests that validation errors provide clear, specific messages
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testMessagesValidationExplicites(bool $curl): void
    {
        $this->postConnexion($curl);

        // Test missing field error message
        $this->requestApi(
            $curl,
            'POST',
            '/occasion',
            $statusCode,
            $body,
            '',
            ['date' => '2025-01-01'] // Missing 'titre'
        );

        $this->assertEquals(400, $statusCode);
        $this->assertStringContainsString('titre', strtolower($body['message']));
        $this->assertStringContainsString('manquant', strtolower($body['message']));
    }

    /**
     * Tests error handling for POST with invalid user already participating
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function test400UtilisateurDejaParticipant(bool $curl): void
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $user = $this->creeUtilisateurEnBase('user');
        $occasion = $this->creeOccasionEnBase(['participants' => [$user]]);

        $this->postConnexion($curl, $admin);

        // Try to add user who is already a participant
        $this->requestApi(
            $curl,
            'POST',
            "/occasion/{$occasion->getId()}/participant",
            $statusCode,
            $body,
            '',
            ['idParticipant' => $user->getId()]
        );

        $this->assertEquals(400, $statusCode);
        $this->assertEquals(['message' => "l'utilisateur participe déjà à l'occasion"], $body);
    }

    /**
     * Tests 400 Bad Request for draw on occasion that already has a draw
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function test400TirageDejaLance(bool $curl): void
    {
        $admin = $this->creeUtilisateurEnBase('admin', ['admin' => true]);
        $user1 = $this->creeUtilisateurEnBase('user1');
        $user2 = $this->creeUtilisateurEnBase('user2');
        $occasion = $this->creeOccasionEnBase(['participants' => [$user1, $user2]]);

        $this->postConnexion($curl, $admin);

        // Create first draw
        $this->requestApi(
            $curl,
            'POST',
            "/occasion/{$occasion->getId()}/tirage",
            $statusCode,
            $body,
            '',
            []
        );
        $this->assertEquals(200, $statusCode);

        // Try to create another draw without force flag
        $this->requestApi(
            $curl,
            'POST',
            "/occasion/{$occasion->getId()}/tirage",
            $statusCode,
            $body,
            '',
            []
        );

        $this->assertEquals(400, $statusCode);
        $this->assertEquals(['message' => 'des résultats existent déjà pour cette occasion'], $body);
    }
}
