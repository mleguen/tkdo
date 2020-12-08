<?php

namespace Tests\Application\Command;

use App\Application\Command\NotifCommand;
use App\Domain\Utilisateur\PrefNotifIdees;
use App\Domain\Utilisateur\Utilisateur;
use App\Infrastructure\Persistence\Idee\DoctrineIdee;
use DateTime;
use Prophecy\Argument;
use Prophecy\Prophecy\MethodProphecy;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\TestCase;

class NotifCommandTest extends TestCase {
  private $commandTester;

  public function setUp(): void
  {
    parent::setUp();
    $application = new Application();

    $application->add($this->container->get(NotifCommand::class));
    $this->commandTester = new CommandTester($application->find('notif'));
  }

  public function testExecute()
  {
    $testCase = $this;
    $startTime = new DateTime();

    /** @var MethodProphecy */
    $mp = $this->utilisateurRepositoryProphecy->readAllByNotifPeriodique(PrefNotifIdees::Quotidienne, Argument::cetera());
    $mp->willReturn([$this->alice, $this->bob, $this->charlie])
      ->shouldBeCalledOnce();

    /** @var MethodProphecy */
    $mp = $this->ideeRepositoryProphecy->readAllByNotifPeriodique($this->alice);
    $ideeCreee = (new DoctrineIdee(1))
      ->setUtilisateur($this->bob)
      ->setDescription('idée créée')
      ->setAuteur($this->bob)
      ->setDateProposition(new DateTime('yesterday'));
    $ideeSupprimee = (new DoctrineIdee(2))
      ->setUtilisateur($this->charlie)
      ->setDescription('idée supprimée')
      ->setAuteur($this->bob)
      ->setDateProposition(new DateTime('1 month ago'))
      ->setDateSuppression(new DateTime('today'));
    $mp->willReturn([$ideeCreee, $ideeSupprimee])
      ->shouldBeCalledOnce();

    /** @var MethodProphecy */
    $mp = $this->ideeRepositoryProphecy->readAllByNotifPeriodique($this->bob);
    $mp->willReturn([])
      ->shouldBeCalledOnce();

    /** @var MethodProphecy */
    $mp = $this->ideeRepositoryProphecy->readAllByNotifPeriodique($this->charlie);
    $mp->willReturn([])
      ->shouldBeCalledOnce();

    /** @var MethodProphecy */
    $mp = $this->mailerServiceProphecy->envoieMailNotificationPeriodique($this->alice, [$ideeCreee, $ideeSupprimee]);
    $mp->willReturn(true)
      ->shouldBeCalledOnce();

    /** @var MethodProphecy */
    $mp = $this->mailerServiceProphecy->envoieMailNotificationPeriodique($this->bob, []);
    $mp->willReturn(true)
      ->shouldBeCalledOnce();

    /** @var MethodProphecy */
    $mp = $this->mailerServiceProphecy->envoieMailNotificationPeriodique($this->charlie, []);
    $mp->willReturn(false)
      ->shouldBeCalledOnce();

    /** @var MethodProphecy */
    $mp = $this->utilisateurRepositoryProphecy->update(Argument::type(Utilisateur::class));
    $updated = [];
    $mp->will(function (array $args) use ($testCase, $startTime, &$updated) {
      /** @var Utilisateur */
      $utilisateur = $args[0];
      $updated[] = $utilisateur->getId();
      $testCase->assertGreaterThanOrEqual($startTime, $utilisateur->getDateDerniereNotifPeriodique());
      $testCase->assertLessThanOrEqual(new DateTime(), $utilisateur->getDateDerniereNotifPeriodique());
      return $utilisateur;
    })
      ->shouldBeCalledTimes(2);

    $this->commandTester->execute([
      '--periode' => PrefNotifIdees::Quotidienne,
    ]);

    $this->assertContains($this->alice->getId(), $updated);
    $this->assertContains($this->bob->getId(), $updated);
    $this->assertNotContains($this->charlie->getId(), $updated);
  }
}
