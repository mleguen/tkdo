<?php

declare(strict_types=1);

use App\Appli\Command\FixturesCommand;
use App\Appli\Command\NotifCommand;
use App\Bootstrap;
use Symfony\Component\Console\Application;

require __DIR__ . '/../vendor/autoload.php';

$bootstrap = new Bootstrap();
$bootstrap->initEnv();
$container = $bootstrap->initContainer();

$symfonyConsoleApp = new Application();

foreach ([
    FixturesCommand::class,
    NotifCommand::class,
] as $class) {
    $symfonyConsoleApp->add($container->get($class));
}

$status = $symfonyConsoleApp->run();
exit($status);
