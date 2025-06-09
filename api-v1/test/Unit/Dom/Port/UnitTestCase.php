<?php

declare(strict_types=1);

namespace Test\Unit\Dom\Port;

use PHPUnit\Framework\TestCase;

class UnitTestCase extends TestCase
{
    public static function provideDataTestAdmin(): array
    {
        return [
            ['admin' => false],
            ['admin' => true],
        ];
    }
}