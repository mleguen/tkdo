<?php
declare(strict_types=1);

use App\Application\Command\FixturesCommand;
use App\Application\Command\NotifCommand;
use App\Application\Service\MailerService;
use DI\ContainerBuilder;
use Monolog\Logger;
use Slim\Psr7\Factory\UriFactory;

if (!defined('APP_ROOT')) define('APP_ROOT', __DIR__ . '/..');

return function (ContainerBuilder $containerBuilder) {

    $devMode = boolval($_ENV['TKDO_DEV_MODE'] ?? '1');
    $baseUri = (new UriFactory())->createUri($_ENV['TKDO_BASE_URI'] ?? 'http://localhost:4200');

    // Global Settings Object
    $containerBuilder->addDefinitions([
        'settings' => [
            'auth' => [
                'algo' => 'RS256',
                'fichierClePrivee' => APP_ROOT . '/var/auth/auth_rsa',
                'fichierClePublique' => APP_ROOT . '/var/auth/auth_rsa.pub',
                'validite' => 3600,
            ],
            'commands' => [
                FixturesCommand::class,
                NotifCommand::class,
            ],
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
                    'host' => $_ENV['MYSQL_HOST'],
                    'port' => $_ENV['MYSQL_PORT'],
                    'dbname' => $_ENV['MYSQL_DATABASE'],
                    'user' => $_ENV['MYSQL_USER'],
                    'password' => $_ENV['MYSQL_PASSWORD'],
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
                'path' => boolval($_ENV['TKDO_LOG_TO_STDOUT']) ? 'php://stdout' : APP_ROOT . '/logs/api.log',
                'level' => Logger::DEBUG,
            ],
            'mailer' => [
                'mode' => $devMode ? MailerService::MODE_FILE : MailerService::MODE_MAIL,
                'from' => $_ENV['TKDO_MAILER_FROM'] ?? '',
                'path' => APP_ROOT . '/var/mail',
                'baseUri' => $baseUri,
            ],
            'fixtures' => [
                'host' => $baseUri->getHost()
            ]
        ],
    ]);
};
