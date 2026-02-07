<?php

declare(strict_types=1);

namespace App\Appli\Controller;

/**
 * Shared cookie configuration for auth controllers.
 *
 * Centralizes the dev-mode and API base path resolution used when
 * setting or clearing the tkdo_jwt cookie.
 */
trait CookieConfigTrait
{
    /**
     * Whether the Secure flag should be omitted (dev mode, HTTP testing).
     * Defaults to false (Secure ON). Set TKDO_DEV_MODE=1 to disable.
     */
    private function isDevMode(): bool
    {
        return boolval(getenv('TKDO_DEV_MODE'));
    }

    /**
     * Cookie path derived from TKDO_API_BASE_PATH.
     * Defaults to "/" when not set (direct API access).
     */
    private function getCookiePath(): string
    {
        $apiBasePathEnv = getenv('TKDO_API_BASE_PATH');
        return $apiBasePathEnv !== false ? $apiBasePathEnv : '/';
    }

    /**
     * Build the Secure flag fragment for the Set-Cookie header.
     */
    private function getSecureFlag(): string
    {
        return $this->isDevMode() ? '' : 'Secure; ';
    }
}
