<?php

namespace App\Appli\Command;

use App\Appli\Fixture\ExclusionFixture;
use App\Appli\Fixture\IdeeFixture;
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
        private readonly IdeeFixture $ideeFixture,
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
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $adminEmail = $input->hasOption('admin-email') ? $input->getOption('admin-email') : null;
        
        $loader = new Loader();
        $loader->addFixture($this->utilisateurFixture->setAdminEmail($adminEmail)->setOutput($output));
        $loader->addFixture($this->exclusionFixture->setOutput($output));
        $loader->addFixture($this->occasionFixture->setOutput($output));
        $loader->addFixture($this->ideeFixture->setOutput($output));
        $loader->addFixture($this->resultatFixture->setOutput($output));

        $executor = new ORMExecutor($this->em, new ORMPurger());
        $output->writeln(['Initialisation ou réinitialisation de la base de données (' . ($this->devMode ? 'dev' : 'production') . ')...']);
        $executor->execute($loader->getFixtures());
        $output->writeln(['OK']);

        return 0;
    }
}