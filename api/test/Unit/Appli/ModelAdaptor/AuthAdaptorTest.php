<?php

declare(strict_types=1);

namespace Test\Unit\Appli\ModelAdaptor;

use App\Appli\ModelAdaptor\AuthAdaptor;
use App\Dom\Model\Utilisateur;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class AuthAdaptorTest extends TestCase
{
    use ProphecyTrait;

    public function testConstructorWithGroupeAdminIds(): void
    {
        $auth = new AuthAdaptor(1, false, [10, 20], [10]);

        $this->assertEquals([10, 20], $auth->getGroupeIds());
        $this->assertEquals([10], $auth->getGroupeAdminIds());
    }

    public function testConstructorDefaultsGroupeAdminIdsToEmptyArray(): void
    {
        $auth = new AuthAdaptor(1, false, [10]);

        $this->assertEquals([], $auth->getGroupeAdminIds());
    }

    public function testConstructorDefaultsBothGroupeArraysToEmpty(): void
    {
        $auth = new AuthAdaptor(1, true);

        $this->assertEquals([], $auth->getGroupeIds());
        $this->assertEquals([], $auth->getGroupeAdminIds());
    }

    public function testFromUtilisateurWithGroupeAdminIds(): void
    {
        $utilisateur = $this->prophesize(Utilisateur::class);
        $utilisateur->getId()->willReturn(42);
        $utilisateur->getAdmin()->willReturn(false);

        $auth = AuthAdaptor::fromUtilisateur($utilisateur->reveal(), [10, 20], [10]);

        $this->assertEquals(42, $auth->getIdUtilisateur());
        $this->assertFalse($auth->estAdmin());
        $this->assertEquals([10, 20], $auth->getGroupeIds());
        $this->assertEquals([10], $auth->getGroupeAdminIds());
    }

    public function testFromUtilisateurDefaultsGroupeAdminIdsToEmptyArray(): void
    {
        $utilisateur = $this->prophesize(Utilisateur::class);
        $utilisateur->getId()->willReturn(42);
        $utilisateur->getAdmin()->willReturn(true);

        $auth = AuthAdaptor::fromUtilisateur($utilisateur->reveal(), [5]);

        $this->assertEquals([5], $auth->getGroupeIds());
        $this->assertEquals([], $auth->getGroupeAdminIds());
    }

    public function testGetIdUtilisateur(): void
    {
        $auth = new AuthAdaptor(99, false);

        $this->assertEquals(99, $auth->getIdUtilisateur());
    }

    public function testEstAdmin(): void
    {
        $authAdmin = new AuthAdaptor(1, true);
        $authNonAdmin = new AuthAdaptor(2, false);

        $this->assertTrue($authAdmin->estAdmin());
        $this->assertFalse($authNonAdmin->estAdmin());
    }

    public function testEstUtilisateur(): void
    {
        $utilisateur = $this->prophesize(Utilisateur::class);
        $utilisateur->getId()->willReturn(42);

        $otherUtilisateur = $this->prophesize(Utilisateur::class);
        $otherUtilisateur->getId()->willReturn(99);

        $auth = new AuthAdaptor(42, false);

        $this->assertTrue($auth->estUtilisateur($utilisateur->reveal()));
        $this->assertFalse($auth->estUtilisateur($otherUtilisateur->reveal()));
    }
}
