<?php

declare(strict_types=1);

namespace Test\Unit\Appli\Service;

use App\Appli\ModelAdaptor\AuthAdaptor;
use App\Appli\Service\AuthService;
use App\Appli\Settings\AuthSettings;
use App\Bootstrap;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPUnit\Framework\TestCase;

class AuthServiceTest extends TestCase
{
    private AuthService $authService;
    private AuthSettings $settings;

    protected function setUp(): void
    {
        $bootstrap = new Bootstrap();
        $this->settings = new AuthSettings($bootstrap);
        $this->authService = new AuthService($this->settings);
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

    public function testDecodeTokenWithEmptyGroupeAdminIdsDefaultsToEmptyArray(): void
    {
        // Encode a token where groupeAdminIds defaults to [] via constructor
        // Note: this does NOT test truly old tokens (missing the claim entirely),
        // because encode() always writes groupe_admin_ids to the payload.
        // Backward compatibility with old tokens lacking the claim is covered
        // by the isset() fallback in decode().
        $auth = new AuthAdaptor(42, false, [10, 20]);

        $token = $this->authService->encode($auth);
        $decoded = $this->authService->decode($token);

        $this->assertEquals([], $decoded->getGroupeAdminIds());
    }

    public function testDecodeTokenMissingGroupeAdminIdsClaimDefaultsToEmptyArray(): void
    {
        // Manually construct a JWT payload WITHOUT groupe_admin_ids claim,
        // simulating a token generated before Story 2.2 was deployed
        /** @var string $privateKey */
        $privateKey = file_get_contents($this->settings->fichierClePrivee);

        $payload = [
            'sub' => 42,
            'exp' => time() + 3600,
            'adm' => false,
            'groupe_ids' => [10, 20],
            // groupe_admin_ids intentionally omitted
        ];
        $token = JWT::encode($payload, $privateKey, $this->settings->algo);

        $decoded = $this->authService->decode($token);

        $this->assertEquals(42, $decoded->getIdUtilisateur());
        $this->assertFalse($decoded->estAdmin());
        $this->assertEquals([10, 20], $decoded->getGroupeIds());
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

    public function testEncodeWithDefaultValiditeProducesCorrectExpClaim(): void
    {
        $auth = new AuthAdaptor(1, false, []);
        $before = time();
        $jwt = $this->authService->encode($auth);
        $after = time();

        $key = new Key(
            file_get_contents($this->settings->fichierClePublique) ?: '',
            $this->settings->algo
        );
        $payload = JWT::decode($jwt, $key);

        $this->assertGreaterThanOrEqual($before + $this->settings->validite, $payload->exp);
        $this->assertLessThanOrEqual($after + $this->settings->validite, $payload->exp);
    }

    public function testEncodeWithValiditeOverrideProducesCorrectExpClaim(): void
    {
        $auth = new AuthAdaptor(1, false, []);
        $customValidite = 604800; // 7 days

        $before = time();
        $jwt = $this->authService->encode($auth, $customValidite);
        $after = time();

        $key = new Key(
            file_get_contents($this->settings->fichierClePublique) ?: '',
            $this->settings->algo
        );
        $payload = JWT::decode($jwt, $key);

        $this->assertGreaterThanOrEqual($before + $customValidite, $payload->exp);
        $this->assertLessThanOrEqual($after + $customValidite, $payload->exp);
    }

    public function testGetValiditeSeSouvenirReturnsSevenDays(): void
    {
        $this->assertEquals(604800, $this->authService->getValiditeSeSouvenir());
    }
}
