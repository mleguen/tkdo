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
        $this->assertEquals(['message' => 'Code invalide ou expiré'], $body ?: []);
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
        $this->assertEquals(['message' => 'Code invalide ou expiré'], $secondBody ?: []);
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
        $this->assertEquals(['message' => 'Code invalide ou expiré'], $body ?: []);
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
}
