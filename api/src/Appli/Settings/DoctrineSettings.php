<?php

declare(strict_types=1);

namespace App\Appli\Settings;

use App\Bootstrap;
use Doctrine\Common\Cache\PhpFileCache;
use Doctrine\ORM\Tools\Setup;

class DoctrineSettings
{
    public $config;
    public $connection;

    public function __construct(
        Bootstrap $bootstrap
    )
    {
        $this->config = Setup::createAnnotationMetadataConfiguration(
            ["$bootstrap->apiRoot/src/Appli/ModelAdaptor"],
            $bootstrap->devMode,
            // make sure the next 2 paths exists and are writable
            "$bootstrap->apiRoot/var/doctrine/proxy",
            new PhpFileCache("$bootstrap->apiRoot/var/doctrine/cache")
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
