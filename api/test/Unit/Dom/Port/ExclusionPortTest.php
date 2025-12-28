<?php

declare(strict_types=1);

namespace Test\Unit\Dom\Port;

use App\Dom\Exception\DoublonExclusionException;
use App\Dom\Exception\PasAdminException;
use App\Dom\Model\Auth;
use App\Dom\Model\Exclusion;
use App\Dom\Model\Utilisateur;
use App\Dom\Port\ExclusionPort;
use App\Dom\Repository\ExclusionRepository;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class ExclusionPortTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy */
    private $exclusionRepositoryProphecy;

    /** @var ObjectProphecy */
    private $authProphecy;
    
    /** @var ObjectProphecy */
    private $exclusionProphecy;
    
    /** @var ObjectProphecy */
    private $quiOffreProphecy;
    
    /** @var ObjectProphecy */
    private $quiNeDoitPasRecevoirProphecy;

    /** @var ExclusionPort */
    private $exclusionPort;

    public function setUp(): void
    {
        $this->exclusionRepositoryProphecy = $this->prophesize(ExclusionRepository::class);

        $this->exclusionPort = new ExclusionPort(
            $this->exclusionRepositoryProphecy->reveal()
        );

        $this->authProphecy = $this->prophesize(Auth::class);

        $this->exclusionProphecy = $this->prophesize(Exclusion::class);
        $this->quiOffreProphecy = $this->prophesize(Utilisateur::class);
        $this->quiNeDoitPasRecevoirProphecy = $this->prophesize(Utilisateur::class);
    }

    public function testCreeExclusion()
    {
        $exclusionAttendue = $this->exclusionProphecy->reveal();
        
        $this->authProphecy->estAdmin()->willReturn(true);

        $testCase = $this;
        $this->exclusionRepositoryProphecy->create(Argument::cetera())
            ->will(function ($args) use ($testCase, $exclusionAttendue) {
                $testCase->assertEquals($testCase->quiOffreProphecy->reveal(), $args[0]);
                $testCase->assertEquals($testCase->quiNeDoitPasRecevoirProphecy->reveal(), $args[1]);

                return $exclusionAttendue;
            })
            ->shouldBeCalledOnce();
        
        $exclusion = $this->exclusionPort->creeExclusion(
            $this->authProphecy->reveal(),
            $this->quiOffreProphecy->reveal(),
            $this->quiNeDoitPasRecevoirProphecy->reveal(),
        );

        $this->assertEquals($exclusionAttendue, $exclusion);
    }

    public function testCreeExclusionPasAdmin()
    {
        $this->authProphecy->estAdmin()->willReturn(false);

        $this->expectException(PasAdminException::class);
        $this->exclusionPort->creeExclusion(
            $this->authProphecy->reveal(),
            $this->quiOffreProphecy->reveal(),
            $this->quiNeDoitPasRecevoirProphecy->reveal(),
        );
    }

    public function testCreeExclusionDoublonExclusion()
    {
        $this->authProphecy->estAdmin()->willReturn(true);
        $this->exclusionRepositoryProphecy->create(Argument::cetera())
            ->willThrow(new DoublonExclusionException())
            ->shouldBeCalledOnce();

        $this->expectException(DoublonExclusionException::class);
        $this->exclusionPort->creeExclusion(
            $this->authProphecy->reveal(),
            $this->quiOffreProphecy->reveal(),
            $this->quiNeDoitPasRecevoirProphecy->reveal(),
        );
    }

    public function testListeExclusions()
    {
        $quiOffre = $this->quiOffreProphecy->reveal();

        $this->authProphecy->estAdmin()->willReturn(true);

        $exclusion1 = $this->prophesize(Exclusion::class)->reveal();
        $exclusion2 = $this->prophesize(Exclusion::class)->reveal();
        $exclusionsAttendues = [$exclusion1, $exclusion2];

        $this->exclusionRepositoryProphecy->readByQuiOffre($quiOffre)
            ->willReturn($exclusionsAttendues)
            ->shouldBeCalledOnce();

        $exclusions = $this->exclusionPort->listeExclusions(
            $this->authProphecy->reveal(),
            $quiOffre
        );

        $this->assertEquals($exclusionsAttendues, $exclusions);
    }

    public function testListeExclusionsPasAdmin()
    {
        $this->authProphecy->estAdmin()->willReturn(false);

        $this->expectException(PasAdminException::class);
        $this->exclusionPort->listeExclusions(
            $this->authProphecy->reveal(),
            $this->quiOffreProphecy->reveal()
        );
    }
}
