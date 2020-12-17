<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__, ['.env.local']);
$dotenv->load();

$env = $_ENV['TKDO_ENV'] ?? 'dev';

$dotenv = Dotenv::createImmutable(__DIR__, [".env.{$_ENV['TKDO_ENV']}", '.env']);
$dotenv->required([
	'MYSQL_DATABASE',
	'MYSQL_HOST',
	'MYSQL_PASSWORD',
	'MYSQL_PORT',
	'MYSQL_USER',
	'TKDO_DEV_MODE',
	'TKDO_LOG_TO_STDOUT',
]);
$dotenv->load();

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

if (false) { // Should be set to true in production
	$containerBuilder->enableCompilation(__DIR__ . '/var/cache');
}

// Set up settings
$settings = require __DIR__ . '/app/settings.php';
$settings($containerBuilder);

// Set up dependencies
$dependencies = require __DIR__ . '/app/dependencies.php';
$dependencies($containerBuilder);

// Set up repositories
$repositories = require __DIR__ . '/app/repositories.php';
$repositories($containerBuilder);

// Build PHP-DI Container instance
return $containerBuilder->build();
