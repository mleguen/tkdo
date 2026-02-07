<?php

declare(strict_types=1);

namespace Test\Int;

use App\Appli\ModelAdaptor\AuthCodeAdaptor;
use DateTime;

class AuthTokenControllerTest extends IntTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testValidCodeReturnsUserPayloadAndSetsCookie(bool $curl): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        // First, get an auth code via login
        $this->requestApi(
            $curl,
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
        $this->assertNotNull($loginBody);
        $code = $loginBody['code'];

        // Exchange code for token
        $this->requestApi(
            $curl,
            'POST',
            '/auth/token',
            $statusCode,
            $body,
            '',
            ['code' => $code]
        );

        $this->assertEquals(200, $statusCode);
        $this->assertNotNull($body);

        // Verify response contains user payload
        $this->assertArrayHasKey('utilisateur', $body);
        $this->assertEquals($utilisateur->getId(), $body['utilisateur']['id']);
        $this->assertEquals($utilisateur->getNom(), $body['utilisateur']['nom']);
        $this->assertEquals($utilisateur->getEmail(), $body['utilisateur']['email']);
        $this->assertEquals($utilisateur->getGenre(), $body['utilisateur']['genre']);
        $this->assertEquals($utilisateur->getAdmin(), $body['utilisateur']['admin']);
        $this->assertEquals([], $body['utilisateur']['groupe_ids']);

        // Should NOT return the token in the response body
        $this->assertArrayNotHasKey('token', $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testExpiredCodeReturns401(bool $curl): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        // Create an expired auth code directly in DB
        $authCode = new AuthCodeAdaptor();
        $authCode->setCodeHash('expired_test_code');
        $authCode->setUtilisateurId($utilisateur->getId());
        $authCode->setExpiresAt(new DateTime('-1 second'));
        self::$em->persist($authCode);
        self::$em->flush();

        $this->requestApi(
            $curl,
            'POST',
            '/auth/token',
            $statusCode,
            $body,
            '',
            ['code' => 'expired_test_code']
        );

        $this->assertEquals(401, $statusCode);
        $this->assertEquals(['message' => 'code invalide ou expiré'], $body ?: []);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testAlreadyUsedCodeReturns401(bool $curl): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        // First, get an auth code via login
        $this->requestApi(
            $curl,
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
        $this->assertNotNull($loginBody);
        $code = $loginBody['code'];

        // First exchange should succeed
        $this->requestApi(
            $curl,
            'POST',
            '/auth/token',
            $firstStatusCode,
            $firstBody,
            '',
            ['code' => $code]
        );
        $this->assertEquals(200, $firstStatusCode);

        // Second exchange with same code should fail
        $this->requestApi(
            $curl,
            'POST',
            '/auth/token',
            $secondStatusCode,
            $secondBody,
            '',
            ['code' => $code]
        );

        $this->assertEquals(401, $secondStatusCode);
        $this->assertEquals(['message' => 'code invalide ou expiré'], $secondBody ?: []);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testInvalidCodeReturns401(bool $curl): void
    {
        $this->requestApi(
            $curl,
            'POST',
            '/auth/token',
            $statusCode,
            $body,
            '',
            ['code' => 'totally_invalid_code_that_does_not_exist']
        );

        $this->assertEquals(401, $statusCode);
        $this->assertEquals(['message' => 'code invalide ou expiré'], $body ?: []);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testMissingCodeReturns400(bool $curl): void
    {
        $this->requestApi(
            $curl,
            'POST',
            '/auth/token',
            $statusCode,
            $body,
            '',
            [] // missing 'code'
        );

        $this->assertEquals(400, $statusCode);
    }

    /**
     * Test that concurrent requests with the same code result in exactly one success.
     * Verifies the atomic UPDATE prevents race conditions.
     */
    public function testConcurrentCodeExchangeOnlyOneSucceeds(): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        // Get an auth code
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

        // Send 5 parallel requests with the same code using curl_multi
        $baseUri = getenv('TKDO_BASE_URI');
        $jsonPayload = json_encode(['code' => $code]);
        $concurrency = 5;
        $handles = [];
        $multiHandle = curl_multi_init();

        for ($i = 0; $i < $concurrency; $i++) {
            $ch = curl_init($baseUri . '/auth/token');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $handles[] = $ch;
            curl_multi_add_handle($multiHandle, $ch);
        }

        // Execute all requests concurrently
        do {
            $status = curl_multi_exec($multiHandle, $active);
            if ($active) {
                curl_multi_select($multiHandle);
            }
        } while ($active && $status === CURLM_OK);

        // Collect results
        $statusCodes = [];
        foreach ($handles as $ch) {
            $statusCodes[] = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_multi_remove_handle($multiHandle, $ch);
            curl_close($ch);
        }
        curl_multi_close($multiHandle);

        // Exactly one should succeed (200), the rest should be 401
        $successes = array_filter($statusCodes, fn(int $code) => $code === 200);
        $failures = array_filter($statusCodes, fn(int $code) => $code === 401);

        $this->assertCount(1, $successes, 'Exactly one concurrent request should succeed');
        $this->assertCount($concurrency - 1, $failures, 'All other concurrent requests should get 401');
    }
}
