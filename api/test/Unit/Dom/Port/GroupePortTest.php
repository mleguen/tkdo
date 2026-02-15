<?php
declare(strict_types=1);

namespace Test\Unit\Dom\Port;

use App\Dom\Model\Appartenance;
use App\Dom\Model\Auth;
use App\Dom\Model\Groupe;
use App\Dom\Port\GroupePort;
use App\Dom\Repository\GroupeRepository;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class GroupePortTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy */
    private $groupeRepositoryProphecy;

    /** @var ObjectProphecy */
    private $authProphecy;

    /** @var GroupePort */
    private $groupePort;

    public function setUp(): void
    {
        $this->groupeRepositoryProphecy = $this->prophesize(GroupeRepository::class);

        $this->groupePort = new GroupePort(
            $this->groupeRepositoryProphecy->reveal()
        );

        $this->authProphecy = $this->prophesize(Auth::class);
        $this->authProphecy->getIdUtilisateur()->willReturn(42);
    }

    public function testListeGroupesUtilisateurSeparatesActiveAndArchived(): void
    {
        $groupeActif1 = $this->prophesize(Groupe::class);
        $groupeActif1->getArchive()->willReturn(false);
        $groupeActif2 = $this->prophesize(Groupe::class);
        $groupeActif2->getArchive()->willReturn(false);
        $groupeArchive = $this->prophesize(Groupe::class);
        $groupeArchive->getArchive()->willReturn(true);

        $app1 = $this->prophesize(Appartenance::class);
        $app1->getGroupe()->willReturn($groupeActif1->reveal());
        $app2 = $this->prophesize(Appartenance::class);
        $app2->getGroupe()->willReturn($groupeActif2->reveal());
        $app3 = $this->prophesize(Appartenance::class);
        $app3->getGroupe()->willReturn($groupeArchive->reveal());

        $this->groupeRepositoryProphecy
            ->readToutesAppartenancesForUtilisateur(42)
            ->willReturn([$app1->reveal(), $app2->reveal(), $app3->reveal()])
            ->shouldBeCalledOnce();

        $result = $this->groupePort->listeGroupesUtilisateur($this->authProphecy->reveal());

        $this->assertCount(2, $result['actifs']);
        $this->assertCount(1, $result['archives']);
        $this->assertSame($groupeActif1->reveal(), $result['actifs'][0]);
        $this->assertSame($groupeActif2->reveal(), $result['actifs'][1]);
        $this->assertSame($groupeArchive->reveal(), $result['archives'][0]);
    }

    public function testListeGroupesUtilisateurWithNoGroups(): void
    {
        $this->groupeRepositoryProphecy
            ->readToutesAppartenancesForUtilisateur(42)
            ->willReturn([])
            ->shouldBeCalledOnce();

        $result = $this->groupePort->listeGroupesUtilisateur($this->authProphecy->reveal());

        $this->assertCount(0, $result['actifs']);
        $this->assertCount(0, $result['archives']);
    }

    public function testListeGroupesUtilisateurWithOnlyArchived(): void
    {
        $groupeArchive = $this->prophesize(Groupe::class);
        $groupeArchive->getArchive()->willReturn(true);

        $app = $this->prophesize(Appartenance::class);
        $app->getGroupe()->willReturn($groupeArchive->reveal());

        $this->groupeRepositoryProphecy
            ->readToutesAppartenancesForUtilisateur(42)
            ->willReturn([$app->reveal()])
            ->shouldBeCalledOnce();

        $result = $this->groupePort->listeGroupesUtilisateur($this->authProphecy->reveal());

        $this->assertCount(0, $result['actifs']);
        $this->assertCount(1, $result['archives']);
    }

    public function testListeGroupesUtilisateurPassesCorrectUserId(): void
    {
        $this->authProphecy->getIdUtilisateur()->willReturn(99);

        $this->groupeRepositoryProphecy
            ->readToutesAppartenancesForUtilisateur(99)
            ->willReturn([])
            ->shouldBeCalledOnce();

        $this->groupePort->listeGroupesUtilisateur($this->authProphecy->reveal());
    }
}
