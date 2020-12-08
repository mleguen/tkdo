<?php

use App\Application\Command\NotifCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

require_once __DIR__ . '/../vendor/autoload.php';

/** @var ContainerInterface $container */
$container = require __DIR__ . '/../bootstrap.php';

/** @var NotifCommand */
$command = $container->get(NotifCommand::class);
$status = $command->run(
    new ArrayInput([
        '--periode' => 'Q',
    ]),
    new ConsoleOutput()
);

exit($status);
