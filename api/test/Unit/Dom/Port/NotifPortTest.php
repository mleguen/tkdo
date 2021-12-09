<?php

declare(strict_types=1);

namespace Test\Unit\Dom\Port;

use App\Dom\Exception\PrefNotifIdeesPasPeriodiqueException;
use App\Dom\Model\Auth;
use App\Dom\Model\Idee;
use App\Dom\Model\PrefNotifIdees;
use App\Dom\Model\Utilisateur;
use App\Dom\Plugin\MailPlugin;
use App\Dom\Port\NotifPort;
use App\Dom\Repository\IdeeRepository;
use App\Dom\Repository\UtilisateurRepository;
use DateTime;
use Iterator;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;

class NotifPortTest extends TestCase
{
    use ProphecyTrait;

    /** @var ObjectProphecy */
    private $ideeRepositoryProphecy;
    /** @var ObjectProphecy */
    private $mailPluginProphecy;
    /** @var ObjectProphecy */
    private $utilisateurRepositoryProphecy;

    /** @var ObjectProphecy */
    private $authProphecy;
    /** @var ObjectProphecy */
    private $ideeProphecy;
    /** @var ObjectProphecy */
    private $utilisateurANotifierProphecy;

    /** @var NotifPort */
    private $notifPort;

    public function setUp(): void
    {
        $this->ideeRepositoryProphecy = $this->prophesize(IdeeRepository::class);
        $this->mailPluginProphecy = $this->prophesize(MailPlugin::class);
        $this->utilisateurRepositoryProphecy = $this->prophesize(UtilisateurRepository::class);

        $this->notifPort = new NotifPort(
            $this->ideeRepositoryProphecy->reveal(),
            $this->mailPluginProphecy->reveal(),
            $this->utilisateurRepositoryProphecy->reveal()
        );

        $this->authProphecy = $this->prophesize(Auth::class);
        $this->ideeProphecy = $this->prophesize(Idee::class);
        $this->utilisateurANotifierProphecy = $this->prophesize(Utilisateur::class);
    }

    /** @dataProvider provideDataEnvoieNotifsPeriodiques */
    public function testEnvoieNotifsPeriodiques(string $periode, bool $mailEnvoye)
    {
        $utilisateurANotifier = $this->utilisateurANotifierProphecy->reveal();
        $this->utilisateurRepositoryProphecy->readAllByNotifPeriodique($periode, Argument::type(DateTime::class))
            ->willReturn([$utilisateurANotifier]);

        $idee = $this->ideeProphecy->reveal();
        $this->ideeRepositoryProphecy->readAllByNotifPeriodique($utilisateurANotifier, Argument::type(DateTime::class))->willReturn([$idee]);

        $this->mailPluginProphecy->envoieMailNotifPeriodique($utilisateurANotifier, [$idee])
            ->willReturn($mailEnvoye)
            ->shouldBeCalledOnce();

        if ($mailEnvoye) {
            $this->utilisateurANotifierProphecy->setDateDerniereNotifPeriodique(Argument::type(DateTime::class))
                ->willReturn($utilisateurANotifier)
                ->shouldBeCalledOnce();
            $this->utilisateurRepositoryProphecy->update($utilisateurANotifier)
                ->willReturn($utilisateurANotifier)
                ->shouldBeCalledOnce();
        }

        $this->notifPort->envoieNotifsPeriodiques($periode);
    }

    public function provideDataEnvoieNotifsPeriodiques(): Iterator
    {
        foreach($this->provideDataMailEnvoye() as $data) {
            yield array_merge([PrefNotifIdees::Quotidienne], $data);
        }
    }

    /** @dataProvider provideDataEnvoieNotifsPeriodiquesPrefNotifIdeesPasPeriodique */
    public function testEnvoieNotifsPeriodiquesPrefNotifIdeesPasPeriodique(string $prefNotifIdees): void
    {
        $this->expectException(PrefNotifIdeesPasPeriodiqueException::class);
        $this->notifPort->envoieNotifsPeriodiques($prefNotifIdees);
    }

    public function provideDataEnvoieNotifsPeriodiquesPrefNotifIdeesPasPeriodique(): Iterator
    {
        foreach ([PrefNotifIdees::Instantanee, 'invalide'] as $prefNotifIdees) {
            yield [$prefNotifIdees];
        }
    }

    /** @dataProvider provideDataMailEnvoye */
    public function testEnvoieNotifsInstantaneesCreation(bool $mailEnvoye)
    {
        $utilisateurAuth = $this->prophesize(Utilisateur::class)->reveal();
        $utilisateurANotifier = $this->utilisateurANotifierProphecy->reveal();

        $this->authProphecy->estUtilisateur($utilisateurAuth)->willReturn(true);
        $this->authProphecy->estUtilisateur($utilisateurANotifier)->willReturn(false);

        $utilisateur = $this->prophesize(Utilisateur::class)->reveal();
        $this->utilisateurRepositoryProphecy->readAllByNotifInstantaneePourIdees($utilisateur)
            ->willReturn([
                $utilisateurANotifier,
                $utilisateurAuth,
            ]);

        $idee = $this->ideeProphecy->reveal();
        $this->ideeProphecy->getUtilisateur()->willReturn($utilisateur);

        $this->mailPluginProphecy->envoieMailIdeeCreation($utilisateurANotifier, $idee)
            ->willReturn($mailEnvoye)
            ->shouldBeCalledOnce();

        $this->notifPort->envoieNotifsInstantaneesCreation($this->authProphecy->reveal(), $idee);
    }

    /** @dataProvider provideDataMailEnvoye */
    public function testEnvoieNotifsInstantaneesSuppression(bool $mailEnvoye)
    {
        $utilisateurAuth = $this->prophesize(Utilisateur::class)->reveal();
        $utilisateurANotifier = $this->utilisateurANotifierProphecy->reveal();

        $this->authProphecy->estUtilisateur($utilisateurAuth)->willReturn(true);
        $this->authProphecy->estUtilisateur($utilisateurANotifier)->willReturn(false);

        $utilisateur = $this->prophesize(Utilisateur::class)->reveal();
        $this->utilisateurRepositoryProphecy->readAllByNotifInstantaneePourIdees($utilisateur)
            ->willReturn([
                $utilisateurANotifier,
                $utilisateurAuth,
            ]);

        $idee = $this->ideeProphecy->reveal();
        $this->ideeProphecy->getUtilisateur()->willReturn($utilisateur);

        $this->mailPluginProphecy->envoieMailIdeeSuppression($utilisateurANotifier, $idee)
            ->willReturn($mailEnvoye)
            ->shouldBeCalledOnce();

        $this->notifPort->envoieNotifsInstantaneesSuppression($this->authProphecy->reveal(), $idee);
    }

    public function provideDataMailEnvoye(): Iterator
    {
        foreach ([true, false] as $mailEnvoye) {
            yield [$mailEnvoye];
        }
    }
}
