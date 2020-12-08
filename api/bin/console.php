<?php

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

require_once __DIR__ . '/../vendor/autoload.php';

/** @var ContainerInterface $container */
$container = require __DIR__ . '/../bootstrap.php';

$application = new Application();

foreach ($container->get('settings')['commands'] as $class) {
    $application->add($container->get($class));
}

$status = $application->run();

exit($status);
