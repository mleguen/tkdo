<?php

namespace App\Appli\Command;

use App\Appli\Fixture\ExclusionFixture;
use App\Appli\Fixture\IdeeFixture;
use App\Appli\Fixture\OccasionFixture;
use App\Appli\Fixture\ResultatFixture;
use App\Appli\Fixture\UtilisateurFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FixturesCommand extends Command
{
    private $em;
    private $exclusionFixture;
    private $ideeFixture;
    private $occasionFixture;
    private $resultatFixture;
    private $utilisateurFixture;

    public function __construct(
        EntityManager $em,
        ExclusionFixture $exclusionFixture,
        IdeeFixture $ideeFixture,
        OccasionFixture $occasionFixture,
        ResultatFixture $resultatFixture,
        UtilisateurFixture $utilisateurFixture
    )
    {
        parent::__construct('fixtures');
        $this->em = $em;
        $this->exclusionFixture = $exclusionFixture;
        $this->ideeFixture = $ideeFixture;
        $this->occasionFixture = $occasionFixture;
        $this->resultatFixture = $resultatFixture;
        $this->utilisateurFixture = $utilisateurFixture;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Initialise la base de données');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $loader = new Loader();
        $loader->addFixture($this->utilisateurFixture->setOutput($output));
        $loader->addFixture($this->exclusionFixture->setOutput($output));
        $loader->addFixture($this->occasionFixture->setOutput($output));
        $loader->addFixture($this->ideeFixture->setOutput($output));
        $loader->addFixture($this->resultatFixture->setOutput($output));

        $executor = new ORMExecutor($this->em, new ORMPurger());
        $output->writeln(['Initialisation ou réinitialisation de la base de données...']);
        $executor->execute($loader->getFixtures());
        $output->writeln(['OK']);

        return 0;
    }
}