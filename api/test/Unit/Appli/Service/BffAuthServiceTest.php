<?php

declare(strict_types=1);

namespace Test\Unit\Appli\Service;

use App\Appli\Service\BffAuthService;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;
use PHPUnit\Framework\TestCase;

class BffAuthServiceTest extends TestCase
{
    public function testExtraitInfoUtilisateurUsesResourceOwner(): void
    {
        $owner = new GenericResourceOwner([
            'sub' => 42,
            'admin' => true,
            'groupe_ids' => [1, 2, 3],
        ], 'sub');

        $mockProvider = $this->createMock(GenericProvider::class);
        $mockToken = $this->createMock(AccessToken::class);

        $mockProvider->method('getResourceOwner')
            ->with($mockToken)
            ->willReturn($owner);

        $service = new BffAuthService($mockProvider);
        $result = $service->extraitInfoUtilisateur($mockToken);

        $this->assertEquals(42, $result['sub']);
        $this->assertTrue($result['adm']);
        $this->assertEquals([1, 2, 3], $result['groupe_ids']);
    }

    public function testExtraitInfoUtilisateurDefaultValues(): void
    {
        $owner = new GenericResourceOwner([
            'sub' => 1,
        ], 'sub');

        $mockProvider = $this->createMock(GenericProvider::class);
        $mockToken = $this->createMock(AccessToken::class);

        $mockProvider->method('getResourceOwner')
            ->willReturn($owner);

        $service = new BffAuthService($mockProvider);
        $result = $service->extraitInfoUtilisateur($mockToken);

        $this->assertEquals(1, $result['sub']);
        $this->assertFalse($result['adm']);
        $this->assertEquals([], $result['groupe_ids']);
    }

    public function testExtraitInfoUtilisateurThrowsOnWrongTokenType(): void
    {
        $mockProvider = $this->createMock(GenericProvider::class);
        $mockToken = $this->createMock(AccessTokenInterface::class);

        $service = new BffAuthService($mockProvider);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('type de token inattendu');

        $service->extraitInfoUtilisateur($mockToken);
    }
}
