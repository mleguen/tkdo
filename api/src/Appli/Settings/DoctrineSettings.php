<?php

declare(strict_types=1);

namespace App\Appli\Settings;

use App\Bootstrap;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

class DoctrineSettings
{
    public \Doctrine\ORM\Configuration $config;
    /** @var array<string, mixed> */
    public array $connection;

    public function __construct(
        Bootstrap $bootstrap
    )
    {
        $this->config = ORMSetup::createAnnotationMetadataConfiguration(
            ["$bootstrap->apiRoot/src/Appli/ModelAdaptor"],
            $bootstrap->devMode,
            // make sure the next 2 paths exist and are writable
            "$bootstrap->apiRoot/var/doctrine/proxy",
            new PhpFilesAdapter('', 0, "$bootstrap->apiRoot/var/doctrine/cache")
        );

        $this->connection = [
            'driver' => 'pdo_mysql',
            'host' => getenv('MYSQL_HOST') ?: ($bootstrap->docker ? 'mysql' : '127.0.0.1'),
            'port' => getenv('MYSQL_PORT') ?: '3306',
            'dbname' => getenv('MYSQL_DATABASE') ?: 'tkdo',
            'user' => getenv('MYSQL_USER') ?: 'tkdo',
            'password' => getenv('MYSQL_PASSWORD') ?: 'mdptkdo',
            'charset' => 'utf8'
        ];
    }
}
