<?php

declare(strict_types=1);

namespace Test\Unit\Appli\Fixture;

use App\Appli\Fixture\AppAbstractFixture;
use App\Bootstrap;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;

/**
 * Unit tests for AppAbstractFixture base class.
 *
 * These tests verify that the perfMode flag is properly propagated to fixture classes.
 * Integration testing of actual fixture data creation is done via the k6 performance
 * tests setup() function which validates data conditions (10+ participants, 20+ ideas).
 */
class AppAbstractFixtureTest extends TestCase
{
    private ConcreteTestFixture $fixture;

    public function setUp(): void
    {
        $bootstrap = $this->createMock(Bootstrap::class);
        $bootstrap->devMode = true;

        $this->fixture = new ConcreteTestFixture($bootstrap);
        $this->fixture->setOutput(new NullOutput());
    }

    public function testPerfModeDefaultsToFalse(): void
    {
        $this->assertFalse($this->fixture->isPerfMode());
    }

    public function testSetPerfModeEnablesFlag(): void
    {
        $this->fixture->setPerfMode(true);

        $this->assertTrue($this->fixture->isPerfMode());
    }

    public function testSetPerfModeDisablesFlag(): void
    {
        $this->fixture->setPerfMode(true);
        $this->fixture->setPerfMode(false);

        $this->assertFalse($this->fixture->isPerfMode());
    }

    public function testSetPerfModeReturnsSelfForChaining(): void
    {
        $result = $this->fixture->setPerfMode(true);

        $this->assertSame($this->fixture, $result);
    }

    public function testSetOutputReturnsSelfForChaining(): void
    {
        $result = $this->fixture->setOutput(new NullOutput());

        $this->assertSame($this->fixture, $result);
    }
}

/**
 * Concrete implementation of AppAbstractFixture for testing.
 * Exposes protected properties through public accessor methods.
 */
class ConcreteTestFixture extends AppAbstractFixture
{
    public function load(ObjectManager $manager): void
    {
        // Not used in unit tests
    }

    public function isPerfMode(): bool
    {
        return $this->perfMode;
    }
}
