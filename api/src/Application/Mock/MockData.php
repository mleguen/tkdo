<?php
declare(strict_types=1);

namespace App\Application\Mock;

class MockData
{
    public static function getToken(): string {
        return "fake-jwt-token";
    }
}
