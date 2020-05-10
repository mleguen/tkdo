<?php
declare(strict_types=1);

use App\Tools\Console\Command\FixturesLoadCommand;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\Migrations\Tools\Console\Command;
use Symfony\Component\Console\Helper\QuestionHelper;

$helperSet = require __DIR__ . '/../cli-config.php';
$helperSet->set(new QuestionHelper(), 'question');
$cli = ConsoleRunner::createApplication($helperSet);

$cli->addCommands([
    // Fixtures
    new FixturesLoadCommand(),

    // Migrations (commandes ne nécessitant pas de base de donnée)
    new Command\DiffCommand(),
	new Command\DumpSchemaCommand(),
	new Command\ExecuteCommand(),
	new Command\GenerateCommand(),
	new Command\LatestCommand(),
	new Command\MigrateCommand(),
	new Command\RollupCommand(),
	new Command\StatusCommand(),
	new Command\UpToDateCommand(),
	new Command\VersionCommand(),
]);

// Runs console application
$cli->run();


