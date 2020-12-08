<?php

namespace App\Application\Command;

use App\Application\Service\MailerService;
use App\Domain\Idee\IdeeRepository;
use App\Domain\Utilisateur\PrefNotifIdees;
use App\Domain\Utilisateur\UtilisateurRepository;
use DateTime;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Envoie les notifications périodiques d'idées créées/supprimées
 * 
 * Usage : ./composer-api console notif -p Q
 */
class NotifCommand extends Command
{
  private $ideeRepository;
  private $logger;
  private $mailer;
  private $utilisateurRepository;

  public function __construct(
    LoggerInterface $logger,
    UtilisateurRepository $utilisateurRepository,
    IdeeRepository $ideeRepository,
    MailerService $mailer
  )
  {
    parent::__construct('notif');
    $this->logger = $logger;
    $this->utilisateurRepository = $utilisateurRepository;
    $this->ideeRepository = $ideeRepository;
    $this->mailer = $mailer;
  }

  protected function configure(): void
  {
    parent::configure();

    $this
      ->setDescription("Envoie les notifications périodiques d'idées créées/supprimées")
      ->addOption(
        'periode',
        'p',
        InputOption::VALUE_REQUIRED,
        'Période des notifications à envoyer (Q = Quotidienne)'
      );
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $periode = $input->getOption('periode');
    $this->logger->info("NotifCommande (periode = $periode)");

    $dateMaxDerniereNotifPeriodique = new DateTime();
    
    switch ($periode) {
      case PrefNotifIdees::Quotidienne:
        $dateMaxDerniereNotifPeriodique->sub(date_interval_create_from_date_string('1 day'));
      break;

      default:
        throw new Exception('Valeur de période incorrecte');
    }
    
    $utilisateursANotifier = $this->utilisateurRepository->readAllByNotifPeriodique($periode, $dateMaxDerniereNotifPeriodique);
    foreach($utilisateursANotifier as $utilisateurANotifier) {
      $idees = $this->ideeRepository->readAllByNotifPeriodique($utilisateurANotifier);
      $this->logger->info("NotifCommande: {$utilisateurANotifier->getNom()} " . count($idees));
      if ($this->mailer->envoieMailNotificationPeriodique(
        $utilisateurANotifier,
        $idees
      )) {
        $utilisateurANotifier->setDateDerniereNotifPeriodique(new DateTime());
        $this->utilisateurRepository->update($utilisateurANotifier);
      };
    }

    $this->logger->info("NotifCommande (fin)");
    return 0;
  }
}