<?php

declare(strict_types=1);

namespace Test\Unit\Dom\Port;

use App\Dom\Exception\IdeeDejaSupprimeeException;
use App\Dom\Exception\IdeePasAuteurException;
use App\Dom\Exception\PasAdminException;
use App\Dom\Model\Idee;
use App\Dom\Repository\IdeeRepository;
use App\Dom\Model\Auth;
use App\Dom\Port\IdeePort;
use App\Dom\Model\Utilisateur;
use App\Dom\Port\NotifPort;
use DateTime;
use Exception;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class IdeePortTest extends UnitTestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy */
    private $ideeRepositoryProphecy;
    /** @var ObjectProphecy */
    private $notifPortProphecy;

    /** @var ObjectProphecy */
    private $authProphecy;
    
    /** @var ObjectProphecy */
    private $auteurProphecy;
    /** @var ObjectProphecy */
    private $utilisateurProphecy;
    
    /** @var ObjectProphecy */
    private $ideeProphecy;

    /** @var IdeePort */
    private $ideePort;

    public function setUp(): void
    {
        $this->ideeRepositoryProphecy = $this->prophesize(IdeeRepository::class);
        $this->notifPortProphecy = $this->prophesize(NotifPort::class);

        $this->ideePort = new IdeePort(
            $this->ideeRepositoryProphecy->reveal(),
            $this->notifPortProphecy->reveal()
        );

        $this->authProphecy = $this->prophesize(Auth::class);

        $this->auteurProphecy = $this->prophesize(Utilisateur::class);
        $this->utilisateurProphecy = $this->prophesize(Utilisateur::class);

        $this->ideeProphecy = $this->prophesize(Idee::class);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideDataTestAdmin')]
    public function testCreeIdee(bool $admin)
    {
        $auteur = $this->auteurProphecy->reveal();
        $utilisateur = $this->utilisateurProphecy->reveal();
        
        $auth = $this->authProphecy->reveal();
        $this->authProphecy->estAdmin()->willReturn($admin);
        $this->authProphecy->estUtilisateur($auteur)->willReturn(!$admin);

        $description = 'nouvelle idée';

        $this->ideeProphecy->setAuteur(Argument::cetera())->shouldNotBeCalled();
        $this->ideeProphecy->setDateProposition(Argument::cetera())->shouldNotBeCalled();
        $this->ideeProphecy->setDateSuppression(Argument::cetera())->shouldNotBeCalled();
        $this->ideeProphecy->setDescription(Argument::cetera())->shouldNotBeCalled();
        $this->ideeProphecy->setUtilisateur(Argument::cetera())->shouldNotBeCalled();
        $ideeAttendue = $this->ideeProphecy->reveal();
        
        $testCase = $this;
        $callTime = new DateTime();
        $this->ideeRepositoryProphecy->create(Argument::cetera())
            ->will(function ($args) use ($testCase, $utilisateur, $description, $auteur, $callTime, $ideeAttendue) {
                $testCase->assertEquals($utilisateur, $args[0]);
                $testCase->assertEquals($description, $args[1]);
                $testCase->assertEquals($auteur, $args[2]);

                $dateProposition = $args[3];
                $testCase->assertGreaterThanOrEqual($callTime, $dateProposition);
                $testCase->assertLessThanOrEqual(new DateTime(), $dateProposition);

                return $ideeAttendue;
            })
            ->shouldBeCalledOnce();
        
        $this->notifPortProphecy->envoieNotifsInstantaneesCreation($auth, $ideeAttendue)->shouldBeCalledOnce();

        $idee = $this->ideePort->creeIdee(
            $auth,
            $utilisateur,
            $description,
            $auteur
        );

        $this->assertEquals($ideeAttendue, $idee);
    }

    public function testCreeIdeePasLAuteur()
    {
        $auteur = $this->auteurProphecy->reveal();

        $this->authProphecy->estAdmin()->willReturn(false);
        $this->authProphecy->estUtilisateur($auteur)->willReturn(false);

        $this->expectException(IdeePasAuteurException::class);
        $this->ideePort->creeIdee(
            $this->authProphecy->reveal(),
            $this->utilisateurProphecy->reveal(),
            'nouvelle idée',
            $auteur
        );
    }

    public function testCreeIdeeEchecCreation()
    {
        $auteur = $this->auteurProphecy->reveal();

        $this->authProphecy->estAdmin()->willReturn(false);
        $this->authProphecy->estUtilisateur($auteur)->willReturn(true);

        $this->ideeRepositoryProphecy->create(Argument::cetera())
            ->willThrow(new Exception('erreur de création'))
            ->shouldBeCalledOnce();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('erreur de création');
        $this->ideePort->creeIdee(
            $this->authProphecy->reveal(),
            $this->utilisateurProphecy->reveal(),
            'nouvelle idée',
            $auteur
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideDataTestAdmin')]
    public function testMarqueIdeeCommeSupprimee(bool $admin)
    {
        $auteur = $this->auteurProphecy->reveal();
        $utilisateur = $this->utilisateurProphecy->reveal();

        $auth = $this->authProphecy->reveal();
        $this->authProphecy->estAdmin()->willReturn($admin);
        $this->authProphecy->estUtilisateur($auteur)->willReturn(!$admin);

        $this->ideeProphecy->getAuteur()->willReturn($auteur);
        $this->ideeProphecy->getUtilisateur()->willReturn($utilisateur);
        $this->ideeProphecy->hasDateSuppression()->willReturn(false);

        $this->ideeProphecy->setAuteur(Argument::cetera())->shouldNotBeCalled();
        $this->ideeProphecy->setDateProposition(Argument::cetera())->shouldNotBeCalled();
        $this->ideeProphecy->setDescription(Argument::cetera())->shouldNotBeCalled();
        $this->ideeProphecy->setUtilisateur(Argument::cetera())->shouldNotBeCalled();
        $ideeAttendue = $this->ideeProphecy->reveal();
        
        $callTime = new DateTime();
        
        $this->ideeProphecy->setDateSuppression(Argument::that(fn($dateSuppression) => ($dateSuppression >= $callTime) && ($dateSuppression <= new DateTime())))
            ->willReturn($ideeAttendue)
            ->shouldBeCalledOnce();

        $this->ideeRepositoryProphecy->update($ideeAttendue)
            ->willReturn($ideeAttendue)
            ->shouldBeCalledOnce();

        $this->notifPortProphecy->envoieNotifsInstantaneesSuppression($auth, $ideeAttendue)->shouldBeCalledOnce();

        $idee = $this->ideePort->marqueIdeeCommeSupprimee(
            $auth,
            $ideeAttendue
        );

        $this->assertEquals($ideeAttendue, $idee);
    }

    public function testMarqueIdeeCommeSupprimeePasLAuteur()
    {
        $auteur = $this->auteurProphecy->reveal();

        $this->authProphecy->estAdmin()->willReturn(false);
        $this->authProphecy->estUtilisateur($auteur)->willReturn(false);

        $this->ideeProphecy->getAuteur()->willReturn($auteur);

        $this->expectException(IdeePasAuteurException::class);
        $this->ideePort->marqueIdeeCommeSupprimee(
            $this->authProphecy->reveal(),
            $this->ideeProphecy->reveal()
        );
    }

    public function testMarqueIdeeCommeSupprimeeDejaSupprimee()
    {
        $auteur = $this->auteurProphecy->reveal();

        $this->authProphecy->estAdmin()->willReturn(false);
        $this->authProphecy->estUtilisateur($auteur)->willReturn(true);

        $this->ideeProphecy->getAuteur()->willReturn($auteur);
        $this->ideeProphecy->hasDateSuppression()->willReturn(true);

        $this->expectException(IdeeDejaSupprimeeException::class);
        $this->ideePort->marqueIdeeCommeSupprimee(
            $this->authProphecy->reveal(),
            $this->ideeProphecy->reveal()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideDataListeIdees')]
    public function testListeIdees(
        bool $estUtilisateur,
        bool $getAdmin,
        ?bool $supprimees = null
    )
    {
        $auteur = $this->auteurProphecy->reveal();
        $utilisateur = $this->utilisateurProphecy->reveal();
        
        $this->authProphecy->estUtilisateur($utilisateur)->willReturn($estUtilisateur);
        $this->authProphecy->estUtilisateur($auteur)->willReturn(false);
        $this->authProphecy->estAdmin()->willReturn($getAdmin);

        $this->ideeProphecy->getUtilisateur()->willReturn($utilisateur);
        $this->ideeProphecy->getAuteur()->willReturn($utilisateur);
        $idee = $this->ideeProphecy->reveal();
        
        $ideeTiersProphecy = $this->prophesize(Idee::class);
        $ideeTiersProphecy->getUtilisateur()->willReturn($utilisateur);
        $ideeTiersProphecy->getAuteur()->willReturn($auteur);
        $ideeTiers = $ideeTiersProphecy->reveal();
        
        $ideeSupprimeeProphecy = $this->prophesize(Idee::class);
        $ideeSupprimeeProphecy->getUtilisateur()->willReturn($utilisateur);
        $ideeSupprimeeProphecy->getAuteur()->willReturn($utilisateur);
        $ideeSupprimee = $ideeSupprimeeProphecy->reveal();
        
        $ideeTiersSupprimeeProphecy = $this->prophesize(Idee::class);
        $ideeTiersSupprimeeProphecy->getUtilisateur()->willReturn($utilisateur);
        $ideeTiersSupprimeeProphecy->getAuteur()->willReturn($auteur);
        $ideeTiersSupprimee = $ideeTiersSupprimeeProphecy->reveal();

        $ideesRepo = [];
        $ideesAttendues = [];
        if ($supprimees !== true) {
            $ideesRepo[] = $idee;
            $ideesRepo[] = $ideeTiers;
            $ideesAttendues[] = $idee;
            if (!$estUtilisateur) $ideesAttendues[] = $ideeTiers;
        }
        if ($supprimees !== false) {
            $ideesRepo[] = $ideeSupprimee;
            $ideesRepo[] = $ideeTiersSupprimee;
            $ideesAttendues[] = $ideeSupprimee;
            if (!$estUtilisateur) $ideesAttendues[] = $ideeTiersSupprimee;
        }

        $this->ideeRepositoryProphecy->readAllByUtilisateur($utilisateur, $supprimees)
            ->willReturn($ideesRepo);

        $idees = $this->ideePort->listeIdees(
            $this->authProphecy->reveal(),
            $utilisateur,
            $supprimees
        );

        $this->assertEquals($ideesAttendues, $idees);
    }

    public static function provideDataListeIdees()
    {
        return [
            // utilisateur, idées non supprimées
            [
                'estUtilisateur' => true,
                'getAdmin' => false,
                'supprimees' => false,
            ],
            // tiers, idées non supprimées
            [
                'estUtilisateur' => false,
                'getAdmin' => false,
                'supprimees' => false,
            ],
            // admin, pour lui-même, idées non supprimées
            [
                'estUtilisateur' => true,
                'getAdmin' => true,
                'supprimees' => false,
            ],
            // admin, pour lui-même, idées supprimées
            [
                'estUtilisateur' => true,
                'getAdmin' => true,
                'supprimees' => true,
            ],
            // admin, pour lui-même, toutes les idées
            [
                'estUtilisateur' => true,
                'getAdmin' => true,
            ],
            // admin, pour un autre, idées non supprimées
            [
                'estUtilisateur' => false,
                'getAdmin' => true,
                'supprimees' => false,
            ],
            // admin, pour un autre, idées supprimées
            [
                'estUtilisateur' => false,
                'getAdmin' => true,
                'supprimees' => true,
            ],
            // admin, pour un autre, toutes les idées
            [
                'estUtilisateur' => false,
                'getAdmin' => true,
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideDataListeIdeesToutesPasAdmin')]
    public function testListeIdeesToutesPasAdmin(
        bool $estUtilisateur,
        ?bool $supprimees = null
    ) {
        $auteur = $this->auteurProphecy->reveal();
        $utilisateur = $this->utilisateurProphecy->reveal();

        $this->authProphecy->estUtilisateur($utilisateur)->willReturn($estUtilisateur);
        $this->authProphecy->estUtilisateur($auteur)->willReturn(false);
        $this->authProphecy->estAdmin()->willReturn(false);

        $this->expectException(PasAdminException::class);
        $this->ideePort->listeIdees(
            $this->authProphecy->reveal(),
            $utilisateur,
            $supprimees
        );
    }

    public static function provideDataListeIdeesToutesPasAdmin()
    {
        return [
            // utilisateur, idées supprimées
            [
                'estUtilisateur' => true,
                'supprimees' => true,
            ],
            // utilisateur, toutes les idées
            [
                'estUtilisateur' => true,
            ],
            // tiers, idées supprimées
            [
                'estUtilisateur' => false,
                'supprimees' => true,
            ],
            // tiers, toutes les idées
            [
                'estUtilisateur' => false,
            ],
        ];
    }
}
