<?php

namespace App\Tools\Console\Command;

use App\Infrastructure\Persistence\Idee\DoctrineIdeeFixture;
use App\Infrastructure\Persistence\Occasion\DoctrineOccasionFixture;
use App\Infrastructure\Persistence\ResultatTirage\DoctrineResultatTirageFixture;
use App\Infrastructure\Persistence\Utilisateur\DoctrineUtilisateurFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixturesLoadCommand extends Command {
  protected function configure()
  {
    $this
      ->setName('fixtures:load')
      ->setDescription('Charge les fixtures dans la base de données');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    /**
     * @var EntityManager
     */
    $em = $this->getHelper('em')->getEntityManager();

    $loader = new Loader();
    $loader->addFixture(new DoctrineUtilisateurFixture($output));
    $loader->addFixture(new DoctrineOccasionFixture($output));
    $loader->addFixture(new DoctrineIdeeFixture($output));
    $loader->addFixture(new DoctrineResultatTirageFixture($output));

    $purger = new ORMPurger();
    $executor = new ORMExecutor($em, $purger);
    $output->writeln(['Purge et chargement des fixtures...']);
    $executor->execute($loader->getFixtures());
    $output->writeln(['OK']);

    return 0;
  }
}