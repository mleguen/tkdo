<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Logger;

if (!defined('APP_ROOT')) define('APP_ROOT', __DIR__ . '/..');

return function (ContainerBuilder $containerBuilder) {
    $devMode = (php_sapi_name() == 'cli') || (php_sapi_name() == 'cli-server');
    $docker = getenv('docker') !== false;

    // Global Settings Object
    $containerBuilder->addDefinitions([
        'settings' => [
            'displayErrorDetails' => $devMode,
            'doctrine' => [
                // if true, metadata caching is forcefully disabled
                'dev_mode' => $devMode,

                // make sure the paths exists and are writable
                'cache_dir' => APP_ROOT . '/var/doctrine/cache',
                'proxy_dir' => APP_ROOT . '/var/doctrine/proxy',

                // you should add any other path containing annotated entity classes
                'metadata_dirs' => [APP_ROOT . '/src/Infrastructure/Persistence'],

                'connection' => [
                    'driver' => 'pdo_mysql',
                    'host' => getenv('MYSQL_HOST') ?: ($docker ? 'mysql' : '127.0.0.1'),
                    'port' => getenv('MYSQL_PORT') ?: '3306',
                    'dbname' => getenv('MYSQL_DATABASE') ?: 'tkdo',
                    'user' => getenv('MYSQL_USER') ?: 'tkdo',
                    'password' => getenv('MYSQL_PASSWORD') ?: 'mdptkdo',
                    'charset' => 'utf8'
                ]
            ],
            'logger' => [
                'name' => 'api',
                'path' => $docker ? 'php://stdout' : APP_ROOT . '/logs/api.log',
                'level' => Logger::DEBUG,
            ],
            'token' => [
                'dureeDeVie' => 3600,
            ],
        ],
    ]);
};
