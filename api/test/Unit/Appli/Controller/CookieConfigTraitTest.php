<?php

declare(strict_types=1);

namespace Test\Unit\Appli\Controller;

use App\Appli\Controller\CookieConfigTrait;
use PHPUnit\Framework\TestCase;

/**
 * Verifies that cookie security flags are set correctly in production mode.
 *
 * CI runs E2E tests over HTTP (TKDO_DEV_MODE=1) because self-signed
 * certificates cannot test the Secure cookie flag (browsers reject
 * Secure cookies on untrusted HTTPS). This unit test compensates by
 * asserting the Secure flag is present when TKDO_DEV_MODE is not set.
 */
class CookieConfigTraitTest extends TestCase
{
    use CookieConfigTrait;

    private ?string $originalDevMode;
    private ?string $originalBasePath;

    protected function setUp(): void
    {
        $this->originalDevMode = getenv('TKDO_DEV_MODE') ?: null;
        $this->originalBasePath = getenv('TKDO_API_BASE_PATH') ?: null;
    }

    protected function tearDown(): void
    {
        if ($this->originalDevMode !== null) {
            putenv("TKDO_DEV_MODE={$this->originalDevMode}");
        } else {
            putenv('TKDO_DEV_MODE');
        }
        if ($this->originalBasePath !== null) {
            putenv("TKDO_API_BASE_PATH={$this->originalBasePath}");
        } else {
            putenv('TKDO_API_BASE_PATH');
        }
    }

    public function testSecureFlagPresentInProductionMode(): void
    {
        putenv('TKDO_DEV_MODE');

        $this->assertStringContainsString('Secure', $this->getSecureFlag());
    }

    public function testSecureFlagAbsentInDevMode(): void
    {
        putenv('TKDO_DEV_MODE=1');

        $this->assertEmpty($this->getSecureFlag());
    }

    public function testCookiePathDefaultsToSlash(): void
    {
        putenv('TKDO_API_BASE_PATH');

        $this->assertEquals('/', $this->getCookiePath());
    }

    public function testCookiePathFromEnv(): void
    {
        putenv('TKDO_API_BASE_PATH=/api');

        $this->assertEquals('/api', $this->getCookiePath());
    }
}
