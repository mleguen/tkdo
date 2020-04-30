<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

class SessionRepository
{
    public function __construct()
    {
        if (!isset($_SESSION['repositories'])) {
            $_SESSION['repositories'] = [];
        }
    }

    protected function &initSessionRepository(string $key, array $defaultData): array {
        if (!isset($_SESSION['repositories'][$key])) {
            $_SESSION['repositories'][$key] = $defaultData;
        }
        return $_SESSION['repositories'][$key];
    }
}
