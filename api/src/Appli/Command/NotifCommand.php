<?php

namespace App\Appli\Command;

use App\Dom\Model\Utilisateur;
use App\Dom\Port\NotifPort;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Envoie les notifications périodiques d'idées créées/supprimées
 * 
 * Usage : ./console notif -p Q
 */
class NotifCommand extends Command
{
    private $logger;
    private $notifPort;

    public function __construct(
        LoggerInterface $logger,
        NotifPort $notifPort
    )
    {
        parent::__construct('notif');
        $this->logger = $logger;
        $this->notifPort = $notifPort;
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
        $output->writeln("NotifCommande (periode = $periode)");

        $this->notifPort->envoieNotifsPeriodiques($periode, function(Utilisateur $utilisateur, array $idees) use ($output) {
            $this->logger->info("NotifCommande: {$utilisateur->getNom()} " . count($idees));
            $output->writeln("NotifCommande: {$utilisateur->getNom()} " . count($idees));
        });

        $this->logger->info("NotifCommande (fin)");
        $output->writeln("NotifCommande (fin)");
        return 0;
    }
}