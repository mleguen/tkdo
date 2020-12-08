<?php

namespace App\Application\Command;

use App\Infrastructure\Persistence\Idee\DoctrineIdeeFixture;
use App\Infrastructure\Persistence\Occasion\DoctrineOccasionFixture;
use App\Infrastructure\Persistence\Resultat\DoctrineResultatFixture;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateurFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FixturesCommand extends Command
{
  private $em;
  private $settings;

  public function __construct(
    EntityManager $em,
    ContainerInterface $c
  )
  {
    parent::__construct('fixtures');
    $this->em = $em;
    $this->settings = $c->get('settings')['fixtures'];
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
    $loader->addFixture(new DoctrineUtilisateurFixture($output, $prod, $this->settings['host'], $adminEmail));
    $loader->addFixture(new DoctrineOccasionFixture($output, $prod));
    $loader->addFixture(new DoctrineIdeeFixture($output, $prod));
    $loader->addFixture(new DoctrineResultatFixture($output, $prod));

    $executor = new ORMExecutor($this->em, new ORMPurger());
    $output->writeln(['Initialisation ou réinitialisation de la base de données (' . ($prod ? 'production' : 'tests') . ')...']);
    $executor->execute($loader->getFixtures());
    $output->writeln(['OK']);

    return 0;
  }
}