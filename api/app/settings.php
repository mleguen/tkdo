<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Logger;

if (!defined('APP_ROOT')) define('APP_ROOT', __DIR__ . '/..');

return function (ContainerBuilder $containerBuilder) {
    $devMode = (php_sapi_name() == 'cli') || (php_sapi_name() == 'cli-server');
    $docker = in_array('docker', array_keys($_ENV));

    // Global Settings Object
    $containerBuilder->addDefinitions([
        'settings' => [
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
                    'host' => $_ENV['MYSQL_HOST'] ?? ($docker ? 'mysql' : '127.0.0.1'),
                    'port' => $_ENV['MYSQL_PORT'] ?? '3306',
                    'dbname' => $_ENV['MYSQL_DATABASE'] ?? 'tkdo',
                    'user' => $_ENV['MYSQL_USER'] ?? 'tkdo',
                    'password' => $_ENV['MYSQL_PASSWORD'] ?? 'mdptkdo',
                    'charset' => 'utf8'
                ]
            ],
            'error' => [
                'displayErrorDetails' => $devMode,
                'logErrors' => true,
                'logErrorDetails' => true
            ],
            'logger' => [
                'name' => 'api',
                'path' => $docker ? 'php://stdout' : APP_ROOT . '/logs/api.log',
                'level' => Logger::DEBUG,
            ],
            'auth' => [
                'algo' => 'RS256',
                'fichierClePrivee' => APP_ROOT . '/var/auth/auth_rsa',
                'fichierClePublique' => APP_ROOT . '/var/auth/auth_rsa.pub',
                'validite' => 3600,
            ],
        ],
    ]);
};
