<?php
declare(strict_types=1);

require 'vendor/autoload.php';

use App\Tools\Console\Command\FixturesLoadCommand;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\Migrations\Tools\Console\ConsoleRunner as MigrationsConsoleRunner;
use Symfony\Component\Console\Helper\QuestionHelper;

/** @var DependencyFactory */
$dependencyFactory = require __DIR__ . '/../cli-config.php';

$helperSet = ConsoleRunner::createHelperSet($dependencyFactory->getEntityManager());
$helperSet->set(new QuestionHelper(), 'question');
$cli = ConsoleRunner::createApplication($helperSet);

MigrationsConsoleRunner::addCommands($cli, $dependencyFactory);
$cli->addCommands([new FixturesLoadCommand($dependencyFactory)]);

// Runs console application
$cli->run();


