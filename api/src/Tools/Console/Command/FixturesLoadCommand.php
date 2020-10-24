<?php

namespace App\Tools\Console\Command;

use App\Infrastructure\Persistence\Idee\DoctrineIdeeFixture;
use App\Infrastructure\Persistence\Occasion\DoctrineOccasionFixture;
use App\Infrastructure\Persistence\Resultat\DoctrineResultatFixture;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateurFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\Migrations\Tools\Console\Command\DoctrineCommand;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixturesLoadCommand extends DoctrineCommand {
  /** @var string */
  protected static $defaultName = 'fixtures:load';

  protected function configure(): void
  {
    parent::configure();

    $this
      ->setDescription('Charge les fixtures dans la base de données');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    /**
     * @var EntityManager
     */
    $em = $this->getDependencyFactory()->getEntityManager();

    $loader = new Loader();
    $loader->addFixture(new DoctrineUtilisateurFixture($output));
    $loader->addFixture(new DoctrineOccasionFixture($output));
    $loader->addFixture(new DoctrineIdeeFixture($output));
    $loader->addFixture(new DoctrineResultatFixture($output));

    $purger = new ORMPurger();
    $executor = new ORMExecutor($em, $purger);
    $output->writeln(['Purge et chargement des fixtures...']);
    $executor->execute($loader->getFixtures());
    $output->writeln(['OK']);

    return 0;
  }
}