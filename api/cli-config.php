<?php

use Doctrine\ORM\EntityManager;
use DI\Container;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;

$config = new PhpFile('migrations.php');

/** @var Container $container */
$container = require_once __DIR__ . '/bootstrap.php';
$entityManager = $container->get(EntityManager::class);

return DependencyFactory::fromEntityManager($config, new ExistingEntityManager($entityManager));
