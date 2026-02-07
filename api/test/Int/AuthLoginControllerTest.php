<?php

declare(strict_types=1);

namespace Test\Int;

class AuthLoginControllerTest extends IntTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testValidCredentialsReturnsCode(bool $curl): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        $this->requestApi(
            $curl,
            'POST',
            '/auth/login',
            $statusCode,
            $body,
            '',
            [
                'identifiant' => $utilisateur->getIdentifiant(),
                'mdp' => $utilisateur->getMdpClair(),
            ]
        );

        $this->assertEquals(200, $statusCode);
        $this->assertNotNull($body);
        $this->assertArrayHasKey('code', $body);
        $this->assertIsString($body['code']);
        // Code should be 64 chars hex (32 bytes)
        $this->assertEquals(64, strlen($body['code']));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $body['code']);
        // Should NOT return a token or user data
        $this->assertArrayNotHasKey('token', $body);
        $this->assertArrayNotHasKey('utilisateur', $body);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testInvalidPasswordReturns400(bool $curl): void
    {
        $utilisateur = $this->utilisateur()->withIdentifiant('utilisateur')->persist(self::$em);

        $this->requestApi(
            $curl,
            'POST',
            '/auth/login',
            $statusCode,
            $body,
            '',
            [
                'identifiant' => $utilisateur->getIdentifiant(),
                'mdp' => 'mauvais' . $utilisateur->getMdpClair(),
            ]
        );

        $this->assertEquals(400, $statusCode);
        $this->assertEquals(['message' => 'identifiants invalides'], $body ?: []);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testUnknownUserReturns400(bool $curl): void
    {
        $this->requestApi(
            $curl,
            'POST',
            '/auth/login',
            $statusCode,
            $body,
            '',
            [
                'identifiant' => 'utilisateur_inconnu',
                'mdp' => 'peuimporte',
            ]
        );

        $this->assertEquals(400, $statusCode);
        $this->assertEquals(['message' => 'identifiants invalides'], $body ?: []);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideCurl')]
    public function testMissingFieldsReturns400(bool $curl): void
    {
        $this->requestApi(
            $curl,
            'POST',
            '/auth/login',
            $statusCode,
            $body,
            '',
            [
                'identifiant' => 'test',
                // missing 'mdp'
            ]
        );

        $this->assertEquals(400, $statusCode);
    }
}
