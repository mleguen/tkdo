<?php

declare(strict_types=1);

use App\Appli\Settings\MigrationSettings;
use App\Bootstrap;
use Doctrine\ORM\EntityManager;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\Migrations\Tools\Console\ConsoleRunner as MigrationsConsoleRunner;
use Symfony\Component\Console\Helper\QuestionHelper;

require __DIR__ . '/../vendor/autoload.php';

$bootstrap = new Bootstrap();
$bootstrap->initEnv();
$container = $bootstrap->initContainer();

/** @var MigrationSettings */
$settings = $container->get(MigrationSettings::class);
$dependencyFactory = DependencyFactory::fromEntityManager(
    $settings->configuration,
    new ExistingEntityManager($container->get(EntityManager::class))
);

$helperSet = ConsoleRunner::createHelperSet($dependencyFactory->getEntityManager());
$helperSet->set(new QuestionHelper(), 'question');
$symfonyConsoleApp = ConsoleRunner::createApplication($helperSet);

MigrationsConsoleRunner::addCommands($symfonyConsoleApp, $dependencyFactory);

// Runs console application
$status = $symfonyConsoleApp->run();
exit($status);
