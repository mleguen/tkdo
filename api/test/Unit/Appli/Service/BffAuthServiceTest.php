<?php

declare(strict_types=1);

namespace Test\Unit\Appli\Service;

use App\Appli\Service\BffAuthService;
use App\Appli\Settings\OAuth2Settings;
use PHPUnit\Framework\TestCase;

class BffAuthServiceTest extends TestCase
{
    public function testExtraitInfoUtilisateurDecodesJwtClaims(): void
    {
        $settings = new OAuth2Settings();
        $service = new BffAuthService($settings);

        // Build a test JWT with known claims
        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $payload = base64_encode(json_encode([
            'sub' => 42,
            'adm' => true,
            'groupe_ids' => [1, 2, 3],
            'exp' => time() + 3600,
        ], JSON_THROW_ON_ERROR));
        $signature = base64_encode('fake-signature');
        $jwt = "$header.$payload.$signature";

        // Create a mock AccessToken
        $mockToken = $this->createMock(\League\OAuth2\Client\Token\AccessTokenInterface::class);
        $mockToken->method('getToken')->willReturn($jwt);

        $result = $service->extraitInfoUtilisateur($mockToken);

        $this->assertEquals(42, $result['sub']);
        $this->assertTrue($result['adm']);
        $this->assertEquals([1, 2, 3], $result['groupe_ids']);
    }

    public function testExtraitInfoUtilisateurDefaultValues(): void
    {
        $settings = new OAuth2Settings();
        $service = new BffAuthService($settings);

        // Build a minimal JWT without optional claims
        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $payload = base64_encode(json_encode([
            'sub' => 1,
            'exp' => time() + 3600,
        ], JSON_THROW_ON_ERROR));
        $signature = base64_encode('fake-signature');
        $jwt = "$header.$payload.$signature";

        $mockToken = $this->createMock(\League\OAuth2\Client\Token\AccessTokenInterface::class);
        $mockToken->method('getToken')->willReturn($jwt);

        $result = $service->extraitInfoUtilisateur($mockToken);

        $this->assertEquals(1, $result['sub']);
        $this->assertFalse($result['adm']);
        $this->assertEquals([], $result['groupe_ids']);
    }

    public function testExtraitInfoUtilisateurThrowsOnInvalidJwt(): void
    {
        $settings = new OAuth2Settings();
        $service = new BffAuthService($settings);

        $mockToken = $this->createMock(\League\OAuth2\Client\Token\AccessTokenInterface::class);
        $mockToken->method('getToken')->willReturn('not-a-jwt');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('access_token JWT invalide');

        $service->extraitInfoUtilisateur($mockToken);
    }
}
