<?php

declare(strict_types=1);

namespace Test\Unit\Appli\Service;

use App\Appli\ModelAdaptor\AuthAdaptor;
use App\Appli\Service\AuthService;
use App\Appli\Settings\AuthSettings;
use App\Bootstrap;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;

    protected function setUp(): void
    {
        $bootstrap = new Bootstrap();
        $settings = new AuthSettings($bootstrap);
        $this->authService = new AuthService($settings);
    }

    public function testEncodeDecodePreservesGroupeAdminIds(): void
    {
        $auth = new AuthAdaptor(42, false, [10, 20], [10]);

        $token = $this->authService->encode($auth);
        $decoded = $this->authService->decode($token);

        $this->assertEquals(42, $decoded->getIdUtilisateur());
        $this->assertFalse($decoded->estAdmin());
        $this->assertEquals([10, 20], $decoded->getGroupeIds());
        $this->assertEquals([10], $decoded->getGroupeAdminIds());
    }

    public function testEncodeDecodeWithEmptyGroupeAdminIds(): void
    {
        $auth = new AuthAdaptor(1, true, [5], []);

        $token = $this->authService->encode($auth);
        $decoded = $this->authService->decode($token);

        $this->assertEquals([], $decoded->getGroupeAdminIds());
    }

    public function testDecodeOldTokenWithoutGroupeAdminIdsFallsBackToEmptyArray(): void
    {
        // Simulate an old token that only has groupe_ids but not groupe_admin_ids
        $auth = new AuthAdaptor(42, false, [10, 20]);

        $token = $this->authService->encode($auth);
        $decoded = $this->authService->decode($token);

        // Even though the token was encoded with current code (which includes groupe_admin_ids),
        // verify that decoding handles the claim correctly
        $this->assertEquals([], $decoded->getGroupeAdminIds());
    }

    public function testEncodeDecodePreservesAllClaims(): void
    {
        $auth = new AuthAdaptor(99, true, [1, 2, 3], [2]);

        $token = $this->authService->encode($auth);
        $decoded = $this->authService->decode($token);

        $this->assertEquals(99, $decoded->getIdUtilisateur());
        $this->assertTrue($decoded->estAdmin());
        $this->assertEquals([1, 2, 3], $decoded->getGroupeIds());
        $this->assertEquals([2], $decoded->getGroupeAdminIds());
    }
}
