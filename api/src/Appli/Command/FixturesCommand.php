<?php

namespace App\Appli\Command;

use App\Appli\Fixture\IdeeFixture;
use App\Appli\Fixture\OccasionFixture;
use App\Appli\Fixture\ResultatFixture;
use App\Appli\Fixture\UtilisateurFixture;
use App\Appli\Service\UriService;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FixturesCommand extends Command
{
  private $em;
  private $host;

  public function __construct(
    EntityManager $em,
    UriService $uriService
  )
  {
    parent::__construct('fixtures');
    $this->em = $em;
    $this->host = $uriService->getHost();
  }

  protected function configure(): void
  {
    parent::configure();

    $this
      ->setDescription('Initialise la base de données')
      ->addOption(
        'prod',
        null,
        InputOption::VALUE_NONE,
        'Jeu de données de production'
      )
      ->addOption(
        'admin-email',
        null,
        InputOption::VALUE_OPTIONAL,
        'E-mail administrateur'
      );
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $prod = $input->getOption('prod');
    $adminEmail = $input->hasOption('admin-email') ? $input->getOption('admin-email') : null;
    
    $loader = new Loader();
    $loader->addFixture(new UtilisateurFixture($output, $prod, $this->host, $adminEmail));
    $loader->addFixture(new OccasionFixture($output, $prod));
    $loader->addFixture(new IdeeFixture($output, $prod));
    $loader->addFixture(new ResultatFixture($output, $prod));

    $executor = new ORMExecutor($this->em, new ORMPurger());
    $output->writeln(['Initialisation ou réinitialisation de la base de données (' . ($prod ? 'production' : 'tests') . ')...']);
    $executor->execute($loader->getFixtures());
    $output->writeln(['OK']);

    return 0;
  }
}