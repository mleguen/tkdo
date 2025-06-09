<?php

declare(strict_types=1);

use App\Appli\Command\NotifCommand;
use App\Bootstrap;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

require __DIR__ . '/../vendor/autoload.php';

$bootstrap = new Bootstrap();
$bootstrap->initEnv();
$container = $bootstrap->initContainer();

/** @var NotifCommand */
$command = $container->get(NotifCommand::class);

$status = $command->run(
    new ArrayInput([
        '--periode' => 'Q',
    ]),
    new ConsoleOutput()
);
exit($status);
