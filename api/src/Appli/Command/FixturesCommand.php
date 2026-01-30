<?php

namespace App\Appli\Command;

use App\Appli\Fixture\ExclusionFixture;
use App\Appli\Fixture\GroupeFixture;
use App\Appli\Fixture\IdeeFixture;
use App\Appli\Fixture\ListeFixture;
use App\Appli\Fixture\OccasionFixture;
use App\Appli\Fixture\ResultatFixture;
use App\Appli\Fixture\UtilisateurFixture;
use App\Bootstrap;
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
    private bool $devMode;

    public function __construct(
        Bootstrap $bootstrap,
        private readonly EntityManager $em,
        private readonly ExclusionFixture $exclusionFixture,
        private readonly GroupeFixture $groupeFixture,
        private readonly IdeeFixture $ideeFixture,
        private readonly ListeFixture $listeFixture,
        private readonly OccasionFixture $occasionFixture,
        private readonly ResultatFixture $resultatFixture,
        private readonly UtilisateurFixture $utilisateurFixture
    )
    {
        parent::__construct('fixtures');
        $this->devMode = $bootstrap->devMode;
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setDescription('Initialise la base de données')
            ->addOption(
                'admin-email',
                null,
                InputOption::VALUE_OPTIONAL,
                'E-mail administrateur'
            )
            ->addOption(
                'perf',
                null,
                InputOption::VALUE_NONE,
                'Ajoute des données pour les tests de performance (10+ participants, 20+ idées)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $adminEmail = $input->hasOption('admin-email') ? $input->getOption('admin-email') : null;
        $perfMode = $input->getOption('perf') === true;

        $loader = new Loader();
        // Loading order: users first, then groups (v2), then visibility (v2), then v1 entities
        $loader->addFixture($this->utilisateurFixture->setAdminEmail($adminEmail)->setPerfMode($perfMode)->setOutput($output));
        $loader->addFixture($this->groupeFixture->setPerfMode($perfMode)->setOutput($output));
        $loader->addFixture($this->listeFixture->setPerfMode($perfMode)->setOutput($output));
        $loader->addFixture($this->exclusionFixture->setPerfMode($perfMode)->setOutput($output));
        $loader->addFixture($this->occasionFixture->setPerfMode($perfMode)->setOutput($output));
        $loader->addFixture($this->ideeFixture->setPerfMode($perfMode)->setOutput($output));
        $loader->addFixture($this->resultatFixture->setPerfMode($perfMode)->setOutput($output));

        $executor = new ORMExecutor($this->em, new ORMPurger());
        $mode = $this->devMode ? 'dev' : 'production';
        if ($perfMode) {
            $mode .= ' + perf';
        }
        $output->writeln(["Initialisation ou réinitialisation de la base de données ({$mode})..."]);
        $executor->execute($loader->getFixtures());
        $output->writeln(['OK']);

        return 0;
    }
}